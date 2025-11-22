<?php
require '../config.php';
session_start();

if (isset($_GET['id']) && isset($_GET['status']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['id'];
    $status_name = $_GET['status'];
    
    // Get status ID
    $stmt = $pdo->prepare("SELECT status_id FROM Reservation_Status WHERE status_name = ?");
    $stmt->execute([$status_name]);
    $status_id = $stmt->fetchColumn();
    
    if ($status_id) {
        $stmt = $pdo->prepare("UPDATE Reservation SET status_id = ? WHERE reservation_id = ?");
        $stmt->execute([$status_id, $id]);
    }
}
header("Location: list.php");
exit;
?>
