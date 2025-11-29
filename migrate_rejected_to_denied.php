<?php
require 'config.php';

echo "<h2>Migration: Rejected → Denied</h2>";
echo "<p>This script will update the status name from 'Rejected' to 'Denied' in the database.</p>";

try {
    // Check if 'Rejected' status exists
    $stmt = $pdo->query("SELECT status_id, status_name FROM Reservation_Status WHERE status_name = 'Rejected'");
    $rejected_status = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rejected_status) {
        echo "<h3>Found 'Rejected' Status</h3>";
        echo "Status ID: " . $rejected_status['STATUS_ID'] . "<br>";
        echo "Status Name: " . $rejected_status['STATUS_NAME'] . "<br><br>";

        // Check if 'Denied' already exists
        $stmt = $pdo->query("SELECT status_id FROM Reservation_Status WHERE status_name = 'Denied'");
        $denied_status = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($denied_status) {
            echo "<h3>⚠️ 'Denied' Status Already Exists</h3>";
            echo "Denied Status ID: " . $denied_status['STATUS_ID'] . "<br><br>";

            // Update reservations to use the Denied status ID, then delete Rejected
            echo "<h3>Migrating Reservations</h3>";
            $stmt = $pdo->prepare("UPDATE Reservation SET status_id = ? WHERE status_id = ?");
            $stmt->execute([$denied_status['STATUS_ID'], $rejected_status['STATUS_ID']]);
            $updated_count = $stmt->rowCount();
            echo "✓ Updated $updated_count reservation(s) from 'Rejected' to 'Denied'<br><br>";

            // Delete the old Rejected status
            echo "<h3>Removing Old 'Rejected' Status</h3>";
            $stmt = $pdo->prepare("DELETE FROM Reservation_Status WHERE status_id = ?");
            $stmt->execute([$rejected_status['STATUS_ID']]);
            echo "✓ Deleted 'Rejected' status<br>";

        } else {
            // Simply rename Rejected to Denied
            echo "<h3>Renaming Status</h3>";
            $stmt = $pdo->prepare("UPDATE Reservation_Status SET status_name = 'Denied' WHERE status_name = 'Rejected'");
            $stmt->execute();
            echo "✓ Renamed 'Rejected' to 'Denied'<br>";
        }

        echo "<br><h3 style='color: green;'>✓ Migration Completed Successfully!</h3>";

    } else {
        echo "<h3 style='color: blue;'>ℹ️ No 'Rejected' Status Found</h3>";
        echo "The database already uses 'Denied' or doesn't have a 'Rejected' status.<br>";
        echo "No migration needed!<br>";
    }

    // Show current statuses
    echo "<br><h3>Current Reservation Statuses</h3>";
    $stmt = $pdo->query("SELECT * FROM Reservation_Status ORDER BY status_id");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Status ID</th><th>Status Name</th></tr>";
    foreach ($statuses as $status) {
        echo "<tr>";
        echo "<td>" . $status['STATUS_ID'] . "</td>";
        echo "<td><strong>" . $status['STATUS_NAME'] . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error</h3>";
    echo "Error: " . $e->getMessage();
}
?>