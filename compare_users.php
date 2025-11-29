<?php
require 'config.php';

echo "<h2>Comparing User Tables</h2>";

echo "<h3>1. APP_USER Table (what FK references)</h3>";
try {
    $stmt = $pdo->query("SELECT * FROM APP_USER");
    $app_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($app_users)) {
        echo "<strong style='color: red;'>APP_USER table is EMPTY!</strong><br>";
    } else {
        echo "<table border='1'>";
        echo "<tr>";
        foreach (array_keys($app_users[0]) as $col) {
            echo "<th>$col</th>";
        }
        echo "</tr>";
        foreach ($app_users as $user) {
            echo "<tr>";
            foreach ($user as $val) {
                echo "<td>$val</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h3>2. \"User\" Table (what app uses)</h3>";
try {
    $stmt = $pdo->query('SELECT * FROM "User"');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<tr>";
    foreach (array_keys($users[0]) as $col) {
        echo "<th>$col</th>";
    }
    echo "</tr>";
    foreach ($users as $user) {
        echo "<tr>";
        foreach ($user as $val) {
            echo "<td>$val</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h3>3. All User-Related Tables</h3>";
try {
    $stmt = $pdo->query("SELECT table_name FROM user_tables WHERE UPPER(table_name) LIKE '%USER%' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>