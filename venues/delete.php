<?php
require '../config.php';
session_start();

if (isset($_GET['id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['id'];
    try {
        // First delete associated reservations to avoid FK constraint violation
        $stmt = $pdo->prepare("DELETE FROM Reservation WHERE venue_id = ?");
        $stmt->execute([$id]);

        // Then delete the venue
        $stmt = $pdo->prepare("DELETE FROM Venue WHERE venue_id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        // In a real app, we might want to redirect with an error message
        // For now, we just fail silently or could log it
    }
}
header("Location: list.php");
exit;
?>
