<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$reservation_id = $_GET['id'] ?? null;

if (!$reservation_id) {
    header("Location: list.php");
    exit;
}

// Verify ownership and status
$stmt = $pdo->prepare("SELECT status_id, start_time FROM Reservation WHERE reservation_id = ? AND user_id = ?");
$stmt->execute([$reservation_id, $_SESSION['user_id']]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    die("Reservation not found or access denied.");
}

// Normalize column keys to lowercase for consistent access across drivers
$reservation = array_change_key_case($reservation, CASE_LOWER);

// Prevent cancelling past reservations
if (strtotime($reservation['start_time']) < time()) {
    die("Cannot cancel past reservations.");
}

// Update status to Cancelled
$stmt = $pdo->query("SELECT status_id FROM Reservation_Status WHERE status_name = 'Cancelled'");
$cancelled_status_id = $stmt->fetchColumn();

$updateStmt = $pdo->prepare("UPDATE Reservation SET status_id = ? WHERE reservation_id = ?");
if ($updateStmt->execute([$cancelled_status_id, $reservation_id])) {
    header("Location: list.php?msg=cancelled");
} else {
    die("Error cancelling reservation.");
}
?>