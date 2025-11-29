<?php
require 'config.php';
session_start();

echo "<h2>User Sync Status</h2>";

echo "<h3>Current Session User</h3>";
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . $_SESSION['user_name'] . "<br>";
} else {
    echo "No user logged in.<br>";
}

echo "<h3>Users in \"User\" table</h3>";
try {
    $stmt = $pdo->query('SELECT USER_ID, USER_NAME, ROLE, DEPARTMENT_ID FROM "User"');
    $users_in_user_table = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<tr><th>USER_ID</th><th>USER_NAME</th><th>ROLE</th><th>DEPARTMENT_ID</th></tr>";
    foreach ($users_in_user_table as $user) {
        echo "<tr>";
        echo "<td>" . $user['USER_ID'] . "</td>";
        echo "<td>" . $user['USER_NAME'] . "</td>";
        echo "<td>" . $user['ROLE'] . "</td>";
        echo "<td>" . $user['DEPARTMENT_ID'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h3>Users in APP_USER table</h3>";
try {
    $stmt = $pdo->query('SELECT USER_ID, USER_NAME, ROLE, DEPARTMENT_ID FROM APP_USER');
    $users_in_app_user = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users_in_app_user)) {
        echo "APP_USER table is EMPTY!<br>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>USER_ID</th><th>USER_NAME</th><th>ROLE</th><th>DEPARTMENT_ID</th></tr>";
        foreach ($users_in_app_user as $user) {
            echo "<tr>";
            echo "<td>" . $user['USER_ID'] . "</td>";
            echo "<td>" . $user['USER_NAME'] . "</td>";
            echo "<td>" . $user['ROLE'] . "</td>";
            echo "<td>" . $user['DEPARTMENT_ID'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h3>Syncing Users</h3>";
try {
    $synced = 0;
    $skipped = 0;

    foreach ($users_in_user_table as $user) {
        // Check if user exists in APP_USER
        $stmt = $pdo->prepare('SELECT USER_ID FROM APP_USER WHERE USER_ID = ?');
        $stmt->execute([$user['USER_ID']]);

        if (!$stmt->fetch()) {
            // User doesn't exist, insert
            $insert = $pdo->prepare('INSERT INTO APP_USER (USER_ID, USER_NAME, PASSWORD_HASH, ROLE, DEPARTMENT_ID) 
                                     SELECT USER_ID, USER_NAME, PASSWORD_HASH, ROLE, DEPARTMENT_ID 
                                     FROM "User" WHERE USER_ID = ?');
            $insert->execute([$user['USER_ID']]);
            echo "✓ Synced user: " . $user['USER_NAME'] . " (ID: " . $user['USER_ID'] . ")<br>";
            $synced++;
        } else {
            echo "- User already exists: " . $user['USER_NAME'] . " (ID: " . $user['USER_ID'] . ")<br>";
            $skipped++;
        }
    }

    echo "<br><strong>Summary:</strong><br>";
    echo "Synced: $synced users<br>";
    echo "Skipped: $skipped users<br>";

    if ($synced > 0) {
        echo "<br><strong style='color: green;'>✓ Sync completed! You can now create reservations.</strong><br>";
    }

} catch (Exception $e) {
    echo "<strong style='color: red;'>Error during sync: " . $e->getMessage() . "</strong><br>";
}

echo "<h3>Session User in APP_USER?</h3>";
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM APP_USER WHERE USER_ID = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $session_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($session_user) {
        echo "<strong style='color: green;'>✓ YES - Your session user exists in APP_USER</strong><br>";
        echo "You should be able to create reservations now.<br>";
    } else {
        echo "<strong style='color: red;'>✗ NO - Your session user does NOT exist in APP_USER</strong><br>";
        echo "Please log out and log back in, or run this script again.<br>";
    }
}
?>