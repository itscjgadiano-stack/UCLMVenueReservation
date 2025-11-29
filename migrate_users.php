<?php
require 'config.php';

echo "<h2>Migrating Users from 'User' to APP_USER</h2>";

try {
    // First, check the structure of both tables
    echo "<h3>Step 1: Checking table structures</h3>";

    $stmt = $pdo->query("SELECT column_name, data_type FROM user_tab_columns WHERE table_name = 'APP_USER' ORDER BY column_id");
    $app_user_cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "APP_USER columns:<br>";
    foreach ($app_user_cols as $col) {
        echo "- " . $col['COLUMN_NAME'] . " (" . $col['DATA_TYPE'] . ")<br>";
    }

    $stmt = $pdo->query("SELECT column_name, data_type FROM user_tab_columns WHERE table_name = 'User' ORDER BY column_id");
    $user_cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<br>User columns:<br>";
    foreach ($user_cols as $col) {
        echo "- " . $col['COLUMN_NAME'] . " (" . $col['DATA_TYPE'] . ")<br>";
    }

    // Now copy the data
    echo "<h3>Step 2: Copying data</h3>";

    $stmt = $pdo->query('SELECT * FROM "User"');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $copied = 0;
    foreach ($users as $user) {
        try {
            $insert_stmt = $pdo->prepare("
                INSERT INTO APP_USER (USER_ID, USER_NAME, PASSWORD_HASH, ROLE, DEPARTMENT_ID)
                VALUES (?, ?, ?, ?, ?)
            ");

            $insert_stmt->execute([
                $user['USER_ID'],
                $user['USER_NAME'],
                $user['PASSWORD_HASH'],
                $user['ROLE'],
                $user['DEPARTMENT_ID']
            ]);

            echo "✓ Copied user: " . $user['USER_NAME'] . " (ID: " . $user['USER_ID'] . ")<br>";
            $copied++;
        } catch (PDOException $e) {
            echo "✗ Failed to copy user " . $user['USER_NAME'] . ": " . $e->getMessage() . "<br>";
        }
    }

    echo "<h3>Step 3: Verification</h3>";
    echo "Total users copied: $copied<br>";

    $stmt = $pdo->query("SELECT COUNT(*) FROM APP_USER");
    $count = $stmt->fetchColumn();
    echo "Users now in APP_USER: $count<br>";

    if ($count > 0) {
        echo "<br><strong style='color: green;'>✓ Migration successful!</strong><br>";
        echo "<a href='reservations/create.php?venue_id=2'>Try creating a reservation now</a>";
    }

} catch (Exception $e) {
    echo "<strong style='color: red;'>Error: " . $e->getMessage() . "</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>