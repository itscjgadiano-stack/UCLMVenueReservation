<?php
require 'config.php';
session_start();

$venue_id = $_GET['venue_id'] ?? 2; // Default to 2 as in your error URL

echo "<h2>Reservation Creation Debug</h2>";

echo "<h3>1. Checking Venue ID: $venue_id</h3>";
try {
    $stmt = $pdo->prepare("SELECT * FROM Venue WHERE venue_id = ?");
    $stmt->execute([$venue_id]);
    $venue = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($venue) {
        echo "✓ Venue EXISTS<br>";
        print_r($venue);
    } else {
        echo "✗ Venue NOT FOUND!<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h3>2. Checking User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</h3>";
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM "User" WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo "✓ User EXISTS<br>";
            echo "USER_ID: " . $user['USER_ID'] . "<br>";
        } else {
            echo "✗ User NOT FOUND!<br>";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

echo "<h3>3. Checking Pending Status</h3>";
try {
    $stmt = $pdo->query("SELECT status_id FROM Reservation_Status WHERE status_name = 'Pending'");
    $status_id = $stmt->fetchColumn();
    if ($status_id) {
        echo "✓ Pending status EXISTS<br>";
        echo "STATUS_ID: $status_id<br>";
    } else {
        echo "✗ Pending status NOT FOUND!<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h3>4. All Reservation Statuses</h3>";
try {
    $stmt = $pdo->query("SELECT * FROM Reservation_Status");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    foreach ($statuses as $status) {
        echo "<tr>";
        foreach ($status as $key => $val) {
            echo "<td><strong>$key:</strong> $val</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h3>5. Test INSERT Query</h3>";
echo "This is what would be inserted:<br>";
echo "venue_id: $venue_id<br>";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "status_id: " . ($status_id ?? 'NOT SET') . "<br>";

// Check foreign key constraints
echo "<h3>6. Foreign Key Constraint Info</h3>";
try {
    $stmt = $pdo->query("SELECT constraint_name, table_name, r_constraint_name 
                         FROM user_constraints 
                         WHERE constraint_name = 'FK_RESERVATION_USER'");
    $fk = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fk) {
        echo "<pre>";
        print_r($fk);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>