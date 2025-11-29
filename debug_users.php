<?php
require 'config.php';

try {
    echo "<h2>Current Users in Database</h2>";
    $stmt = $pdo->query("SELECT * FROM Users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users)) {
        echo "<p>No users found in the database.</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>User ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
        foreach ($users as $user) {
            // Handle case sensitivity for Oracle vs SQLite
            $user = array_change_key_case($user, CASE_LOWER);
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['user_id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<h2>Current Session</h2>";
    session_start();
    if (isset($_SESSION['user_id'])) {
        echo "<p>Logged in User ID: " . $_SESSION['user_id'] . "</p>";
        echo "<p>Logged in Role: " . $_SESSION['role'] . "</p>";
    } else {
        echo "<p>No active session found (or session not started).</p>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>