<?php
require 'config.php';

echo "<h2>Quick Fix: Rename 'Rejected' to 'Denied'</h2>";

try {
    // Simply update the status name
    $stmt = $pdo->prepare("UPDATE Reservation_Status SET status_name = 'Denied' WHERE status_name = 'Rejected'");
    $stmt->execute();

    echo "<p style='color: green; font-size: 1.2em;'><strong>✓ Successfully renamed 'Rejected' to 'Denied'!</strong></p>";

    // Show current statuses
    echo "<h3>Current Statuses:</h3>";
    $stmt = $pdo->query("SELECT * FROM Reservation_Status ORDER BY status_id");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Status ID</th><th>Status Name</th></tr>";
    foreach ($statuses as $status) {
        $highlight = ($status['STATUS_NAME'] == 'Denied') ? "style='background-color: #d4edda;'" : "";
        echo "<tr $highlight>";
        echo "<td>" . $status['STATUS_ID'] . "</td>";
        echo "<td><strong>" . $status['STATUS_NAME'] . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<br><p><a href='reservations/list.php' style='color: #4F46E5; font-size: 1.1em; text-decoration: underline;'>→ Go to Reservations List</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>✗ Error:</strong> " . $e->getMessage() . "</p>";
}
?>