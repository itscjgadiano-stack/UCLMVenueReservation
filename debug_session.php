<?php
require 'config.php';
session_start();

echo "<h2>Session Debug Information</h2>";
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Users in Database:</h3>";
try {
    $stmt = $pdo->query('SELECT user_id, user_name, role FROM "User"');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>USER_ID</th><th>USER_NAME</th><th>ROLE</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['USER_ID'] . "</td>";
        echo "<td>" . $user['USER_NAME'] . "</td>";
        echo "<td>" . $user['ROLE'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h3>Session User Exists in DB?</h3>";
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM "User" WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo "YES - User found:<br>";
            echo "<pre>";
            print_r($user);
            echo "</pre>";
        } else {
            echo "NO - User ID " . $_SESSION['user_id'] . " does NOT exist in database!<br>";
            echo "<strong>This is the problem! You need to log out and log back in.</strong>";
        }
    } catch (Exception $e) {
        echo "Error checking: " . $e->getMessage();
    }
} else {
    echo "No user_id in session.";
}
?>