<?php
require '../config.php';
session_start();

if (isset($_GET['id']) && isset($_GET['status']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['id'];
    $status_name = $_GET['status'];

    try {
        // Get status ID
        $stmt = $pdo->prepare("SELECT status_id FROM Reservation_Status WHERE status_name = ?");
        $stmt->execute([$status_name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Normalize column names for Oracle compatibility
            $result = array_change_key_case($result, CASE_LOWER);
            $status_id = $result['status_id'];

            // Update reservation status
            $stmt = $pdo->prepare("UPDATE Reservation SET status_id = ? WHERE reservation_id = ?");
            if ($stmt->execute([$status_id, $id])) {
                header("Location: list.php?msg=status_updated");
                exit;
            } else {
                header("Location: list.php?error=update_failed");
                exit;
            }
        } else {
            header("Location: list.php?error=invalid_status");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Update status error: " . $e->getMessage());
        header("Location: list.php?error=db_error");
        exit;
    }
} else {
    header("Location: list.php?error=unauthorized");
    exit;
}
?>