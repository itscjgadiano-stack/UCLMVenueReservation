<?php
session_start();
// Simple auth check placeholder
$is_logged_in = isset($_SESSION['user_id']);
$current_page = basename($_SERVER['PHP_SELF']);

// Determine base path
$in_subdir = (dirname($_SERVER['PHP_SELF']) != '/' && dirname($_SERVER['PHP_SELF']) != '\\');
$base_path = $in_subdir ? '../' : './';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UCLM Venue Reservation</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base_path ?>assets/style.css">
</head>
<body>

<div class="app-container">
    <?php if($is_logged_in): ?>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <span class="brand-name">UCLM Venues</span>
        </div>
        
        <nav class="nav-menu">
            <div class="nav-label">Main</div>
            <a href="<?= $base_path ?>index.php" class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
            
            <div class="nav-label">Management</div>
            <a href="<?= $base_path ?>venues/list.php" class="nav-link <?= strpos($current_page, 'venues') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-door-open"></i>
                <span>Venues</span>
            </a>
            <a href="<?= $base_path ?>reservations/list.php" class="nav-link <?= strpos($current_page, 'reservations') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Reservations</span>
            </a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <div class="nav-label">Admin</div>
            <a href="<?= $base_path ?>users/list.php" class="nav-link <?= strpos($current_page, 'users') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </a>
            <?php endif; ?>
        </nav>
        
        <div class="user-profile">
            <div class="avatar">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-info">
                <h4><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h4>
                <span><?= htmlspecialchars($_SESSION['role'] ?? 'Guest') ?></span>
            </div>
            <a href="<?= $base_path ?>auth/logout.php" style="margin-left: auto; color: var(--slate-400); padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s;" title="Logout" onmouseover="this.style.color='var(--danger-500)'; this.style.backgroundColor='#FEF2F2'" onmouseout="this.style.color='var(--slate-400)'; this.style.backgroundColor='transparent'">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </aside>
    <?php endif; ?>

    <main class="main-content" style="<?= !$is_logged_in ? 'margin-left: 0; width: 100%;' : '' ?>">
