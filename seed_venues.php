<?php
require 'config.php';

try {
    // 1. Seed Buildings
    $buildings = [
        'Main Building',
        'Maritime Building',
        'Basic Education Building'
    ];

    foreach ($buildings as $b_name) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO Building (Building_name) VALUES (?)");
        $stmt->execute([$b_name]);
    }

    echo "Buildings seeded.\n";

    // Helper to get building ID
    function getBuildingId($pdo, $name) {
        $stmt = $pdo->prepare("SELECT Building_id FROM Building WHERE Building_name = ?");
        $stmt->execute([$name]);
        return $stmt->fetchColumn();
    }

    $main_id = getBuildingId($pdo, 'Main Building');
    $maritime_id = getBuildingId($pdo, 'Maritime Building');
    $basic_ed_id = getBuildingId($pdo, 'Basic Education Building');

    // 2. Seed Venues
    $venues = [
        ['Old AVR', 1, $main_id],
        ['Maritime New AVR', 1, $maritime_id],
        ['Basic Education Function Hall', 1, $basic_ed_id],
        ['Basic Education Auditorium', 1, $basic_ed_id]
    ];

    foreach ($venues as $venue) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO Venue (venue_name, floor_number, Building_id) VALUES (?, ?, ?)");
        $stmt->execute($venue);
    }

    echo "Venues seeded successfully.\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
