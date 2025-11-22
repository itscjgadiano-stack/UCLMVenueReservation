<?php
require 'config.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

// Fetch basic stats
$stmt = $pdo->query("SELECT COUNT(*) FROM Venue");
$venue_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM Reservation WHERE status_id = (SELECT status_id FROM Reservation_Status WHERE status_name = 'Pending')");
$pending_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM Reservation WHERE start_time >= date('now')");
$upcoming_count = $stmt->fetchColumn();
?>

<div class="page-header">
    <div class="page-title">
        <h1>Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="reservations/create.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Quick Book
        </a>
    </div>
</div>

<div class="grid grid-cols-3 gap-6 mb-4">
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: var(--slate-500); font-size: 0.9rem; font-weight: 500; margin-bottom: 0.25rem;">Total Venues</p>
                <h2 style="font-size: 2rem; font-weight: 700; color: var(--slate-900);"><?= $venue_count ?></h2>
            </div>
            <div style="width: 56px; height: 56px; background-color: var(--primary-50); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: var(--primary-600);">
                <i class="fa-solid fa-building" style="font-size: 1.5rem;"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: var(--slate-500); font-size: 0.9rem; font-weight: 500; margin-bottom: 0.25rem;">Pending Requests</p>
                <h2 style="font-size: 2rem; font-weight: 700; color: var(--slate-900);"><?= $pending_count ?></h2>
            </div>
            <div style="width: 56px; height: 56px; background-color: #FFFBEB; border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: #D97706;">
                <i class="fa-solid fa-clock" style="font-size: 1.5rem;"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: var(--slate-500); font-size: 0.9rem; font-weight: 500; margin-bottom: 0.25rem;">Upcoming Bookings</p>
                <h2 style="font-size: 2rem; font-weight: 700; color: var(--slate-900);"><?= $upcoming_count ?></h2>
            </div>
            <div style="width: 56px; height: 56px; background-color: #ECFDF5; border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: #059669;">
                <i class="fa-solid fa-calendar-day" style="font-size: 1.5rem;"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Activity</h3>
        <a href="reservations/list.php" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Venue</th>
                    <th>Reserved By</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- Placeholder for recent activity logic -->
                <?php
                // Fetch recent 5 reservations
                $user_id = $_SESSION['user_id'];
                $is_admin = ($_SESSION['role'] == 'admin');
                
                $sql = "SELECT r.*, v.venue_name, rs.status_name FROM Reservation r 
                        JOIN Venue v ON r.venue_id = v.venue_id 
                        JOIN Reservation_Status rs ON r.status_id = rs.status_id";
                
                if (!$is_admin) {
                    $sql .= " WHERE r.user_id = $user_id";
                }
                
                $sql .= " ORDER BY r.created_at DESC LIMIT 5";
                
                $stmt = $pdo->query($sql);
                $recent_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <?php if(count($recent_reservations) > 0): ?>
                    <?php foreach($recent_reservations as $res): ?>
                    <tr>
                        <td><span style="font-family: monospace; color: var(--slate-500);">#<?= $res['reservation_id'] ?></span></td>
                        <td><?= htmlspecialchars($res['venue_name']) ?></td>
                        <td><?= htmlspecialchars($res['reserved_by']) ?></td>
                        <td><?= date('M d, Y', strtotime($res['start_time'])) ?></td>
                        <td>
                            <?php
                                $status_class = 'badge-warning';
                                if($res['status_name'] == 'Approved') $status_class = 'badge-success';
                                if($res['status_name'] == 'Rejected' || $res['status_name'] == 'Cancelled') $status_class = 'badge-danger';
                            ?>
                            <span class="badge <?= $status_class ?>"><?= $res['status_name'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 3rem; color: var(--slate-400);">
                            <i class="fa-regular fa-folder-open" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <p>No recent activity found.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
