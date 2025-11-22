<?php
require 'config.php';

try {
    // Check if column exists
    $stmt = $pdo->query("PRAGMA table_info(Reservation)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $exists = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'created_at') {
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        $pdo->exec("ALTER TABLE Reservation ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        echo "Successfully added 'created_at' column to Reservation table.\n";
    } else {
        echo "'created_at' column already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
