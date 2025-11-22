<?php
require '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $building_name = trim($_POST['building_name']);
    if (!empty($building_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Building (Building_name) VALUES (?)");
            $stmt->execute([$building_name]);
        } catch (PDOException $e) {
            // Handle error silently or redirect with error
        }
    }
}
header("Location: add.php");
exit;
?>
