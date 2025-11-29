<?php
require 'config.php';

echo "<h2>Reservation Status Diagnostic</h2>";

echo "<h3>1. Current Reservation Statuses</h3>";
try {
    $stmt = $pdo->query("SELECT * FROM Reservation_Status ORDER BY status_id");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($statuses)) {
        echo "<strong style='color: red;'>✗ No statuses found!</strong><br>";
    } else {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Status ID</th><th>Status Name</th></tr>";
        foreach ($statuses as $status) {
            echo "<tr>";
            echo "<td>" . $status['STATUS_ID'] . "</td>";
            echo "<td><strong>" . $status['STATUS_NAME'] . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>2. Check if 'Rejected' status exists</h3>";
try {
    $stmt = $pdo->query("SELECT * FROM Reservation_Status WHERE status_name = 'Rejected'");
    $rejected = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rejected) {
        echo "<strong style='color: green;'>✓ 'Rejected' status exists</strong><br>";
        echo "Status ID: " . $rejected['STATUS_ID'] . "<br>";
    } else {
        echo "<strong style='color: red;'>✗ 'Rejected' status NOT found!</strong><br>";
        echo "<p>This is the problem! The 'Rejected' status doesn't exist in the database.</p>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>3. Fix Missing Statuses</h3>";
echo "<form method='POST'>";
echo "<p>Click below to ensure all required statuses exist:</p>";
echo "<button type='submit' name='fix_statuses' style='padding: 10px 20px; background: #4F46E5; color: white; border: none; border-radius: 5px; cursor: pointer;'>Fix Statuses</button>";
echo "</form>";

if (isset($_POST['fix_statuses'])) {
    try {
        $required_statuses = ['Pending', 'Approved', 'Rejected', 'Cancelled'];

        foreach ($required_statuses as $status_name) {
            // Check if status exists
            $stmt = $pdo->prepare("SELECT status_id FROM Reservation_Status WHERE status_name = ?");
            $stmt->execute([$status_name]);

            if (!$stmt->fetch()) {
                // Status doesn't exist, create it
                $stmt = $pdo->prepare("INSERT INTO Reservation_Status (status_name) VALUES (?)");
                $stmt->execute([$status_name]);
                echo "<p style='color: green;'>✓ Created status: <strong>$status_name</strong></p>";
            } else {
                echo "<p style='color: blue;'>- Status already exists: <strong>$status_name</strong></p>";
            }
        }

        echo "<br><strong style='color: green; font-size: 1.2em;'>✓ All statuses are now in place!</strong><br>";
        echo "<p><a href='reservations/list.php' style='color: #4F46E5; text-decoration: underline;'>Go back to Reservations</a></p>";

    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
}

echo "<br><h3>4. Recent Reservations</h3>";
try {
    $stmt = $pdo->query("SELECT r.reservation_id, r.reserved_by, rs.status_name 
                         FROM Reservation r 
                         JOIN Reservation_Status rs ON r.status_id = rs.status_id 
                         ORDER BY r.reservation_id DESC 
                         FETCH FIRST 5 ROWS ONLY");
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($reservations)) {
        echo "No reservations found.<br>";
    } else {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Event</th><th>Status</th></tr>";
        foreach ($reservations as $res) {
            echo "<tr>";
            echo "<td>#" . $res['RESERVATION_ID'] . "</td>";
            echo "<td>" . htmlspecialchars($res['RESERVED_BY']) . "</td>";
            echo "<td><strong>" . $res['STATUS_NAME'] . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>