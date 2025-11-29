<?php
// migrate_users_to_oracle.php
// Usage: edit the Oracle connection variables below, then run:
// php migrate_users_to_oracle.php

/*
 This script copies users from the local SQLite `database.sqlite` into the
 Oracle "User" table in the specified Oracle schema. It preserves
 the `password_hash` and `role`. Departments are matched by name; if a
 department in SQLite doesn't exist in Oracle it will be created.

 IMPORTANT: Run this AFTER you've created the Oracle schema (run init_oracle.sql)
 and AFTER verifying that you want to copy the users. A backup of the Oracle
 tables is recommended.
*/

// === Configure your Oracle connection here ===
$oracle_tns = "//localhost:1521/XEPDB1"; // e.g. //host:1521/servicename
$oracle_user = 'system';
$oracle_pass = 'chrystal09';

// Path to local sqlite file (default project location)
$sqlite_file = __DIR__ . '/database.sqlite';

try {
    // SQLite connection (read-only)
    $sqlite = new PDO('sqlite:' . $sqlite_file);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Oracle connection
    $oracle = new PDO("oci:dbname={$oracle_tns}", $oracle_user, $oracle_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Connected to both databases.\n";

    // Load departments from SQLite (id => name)
    $stmt = $sqlite->query('SELECT department_id, department_name FROM Department');
    $sqliteDepts = [];
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sqliteDepts[$r['department_id']] = $r['department_name'];
    }

    // Prepare helpers for Oracle: lookup dept by name, insert if missing
    $orcDeptSelect = $oracle->prepare('SELECT department_id FROM Department WHERE department_name = ?');
    $orcDeptInsert = $oracle->prepare('INSERT INTO Department (department_name) VALUES (?)');

    // Prepare Oracle user lookup/insert
    $orcUserSelect = $oracle->prepare('SELECT user_id FROM "User" WHERE user_name = ?');
    $orcUserInsert = $oracle->prepare('INSERT INTO "User" (user_name, password_hash, role, department_id) VALUES (?, ?, ?, ?)');

    // Fetch users from SQLite
    $uStmt = $sqlite->query('SELECT user_id, user_name, password_hash, role, department_id FROM "User"');
    $count = 0;
    while ($u = $uStmt->fetch(PDO::FETCH_ASSOC)) {
        $username = $u['user_name'];

        // Skip if already exists in Oracle
        $orcUserSelect->execute([$username]);
        if ($orcUserSelect->fetchColumn()) {
            echo "Skipping existing user: {$username}\n";
            continue;
        }

        // Map department id by name
        $deptId = null;
        if (!empty($u['department_id']) && isset($sqliteDepts[$u['department_id']])) {
            $deptName = $sqliteDepts[$u['department_id']];
            // try to find in Oracle
            $orcDeptSelect->execute([$deptName]);
            $deptId = $orcDeptSelect->fetchColumn();
            if (!$deptId) {
                // insert department into Oracle
                $orcDeptInsert->execute([$deptName]);
                // fetch the created id
                $orcDeptSelect->execute([$deptName]);
                $deptId = $orcDeptSelect->fetchColumn();
                echo "Inserted department in Oracle: {$deptName} (id={$deptId})\n";
            }
        }

        // Insert user into Oracle (preserve password_hash)
        $orcUserInsert->execute([
            $username,
            $u['password_hash'],
            $u['role'],
            $deptId
        ]);
        echo "Inserted user into Oracle: {$username}\n";
        $count++;
    }

    echo "Done. {$count} users inserted.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
