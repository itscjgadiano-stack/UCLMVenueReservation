<?php
require '../config.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$is_admin = ($_SESSION['role'] == 'admin');
$user_id = $_SESSION['user_id'];

// Build Query (use quoted "User" for Oracle and prepared params)
$sql = 'SELECT r.*, v.venue_name, u.user_name, rs.status_name
        FROM Reservation r
        JOIN Venue v ON r.venue_id = v.venue_id
        JOIN "User" u ON r.user_id = u.user_id
        JOIN Reservation_Status rs ON r.status_id = rs.status_id';

$params = [];
if (!$is_admin) {
    $sql .= ' WHERE r.user_id = :user_id';
    $params[':user_id'] = $user_id;
}

$sql .= ' ORDER BY r.start_time DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Normalize keys to lowercase for consistent access across drivers
$reservations = array_map(function ($r) {
    return array_change_key_case($r, CASE_LOWER);
}, $raw);
?>

<div class="page-header">
    <div class="page-title">
        <h1><?= $is_admin ? 'All Reservations' : 'My Reservations' ?></h1>
        <p>Track and manage booking requests</p>
    </div>
    <?php if (!$is_admin): ?>
        <a href="../venues/list.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New Reservation
        </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event</th>
                    <th>Venue</th>
                    <th>Date & Time</th>
                    <?php if ($is_admin): ?>
                        <th>User</th><?php endif; ?>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reservations) > 0): ?>
                    <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td><span
                                    style="font-family: monospace; color: var(--slate-500);">#<?= $res['reservation_id'] ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--slate-900);">
                                    <?= htmlspecialchars($res['reserved_by']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fa-regular fa-building" style="color: var(--slate-400);"></i>
                                    <?= htmlspecialchars($res['venue_name']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?= date('M d, Y', strtotime($res['start_time'])) ?></div>
                                <div style="font-size: 0.8rem; color: var(--slate-500);">
                                    <?= date('h:i A', strtotime($res['start_time'])) ?> -
                                    <?= date('h:i A', strtotime($res['end_time'])) ?>
                                </div>
                            </td>
                            <?php if ($is_admin): ?>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div
                                            style="width: 24px; height: 24px; background-color: var(--slate-200); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 600;">
                                            <?= strtoupper(substr($res['user_name'], 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars($res['user_name']) ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <td>
                                <?php
                                $status_class = 'badge-warning';
                                $icon = 'fa-clock';
                                if ($res['status_name'] == 'Approved') {
                                    $status_class = 'badge-success';
                                    $icon = 'fa-check';
                                }
                                if ($res['status_name'] == 'Rejected' || $res['status_name'] == 'Cancelled') {
                                    $status_class = 'badge-danger';
                                    $icon = 'fa-xmark';
                                }
                                ?>
                                <span class="badge <?= $status_class ?>">
                                    <i class="fa-solid <?= $icon ?>" style="margin-right: 0.25rem;"></i>
                                    <?= $res['status_name'] ?>
                                </span>
                            </td>
                            <?php if ($is_admin): ?>
                                <td>
                                    <?php if ($res['status_name'] == 'Pending'): ?>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="update_status.php?id=<?= $res['reservation_id'] ?>&status=Approved"
                                                class="btn btn-sm btn-primary" style="padding: 0.35rem 0.75rem;" title="Approve">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                            <a href="update_status.php?id=<?= $res['reservation_id'] ?>&status=Rejected"
                                                class="btn btn-sm btn-danger" style="padding: 0.35rem 0.75rem;" title="Deny">
                                                <i class="fa-solid fa-xmark"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php else: ?>
                                <td>
                                    <?php if (strtotime($res['start_time']) > time() && $res['status_name'] != 'Cancelled' && $res['status_name'] != 'Rejected'): ?>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="edit.php?id=<?= $res['reservation_id'] ?>" class="btn btn-sm btn-outline"
                                                title="Edit/Reschedule">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </a>
                                            <a href="cancel.php?id=<?= $res['reservation_id'] ?>" class="btn btn-sm btn-danger"
                                                title="Cancel Booking"
                                                onclick="return confirm('Are you sure you want to cancel this reservation? This action cannot be undone.')">
                                                <i class="fa-solid fa-ban"></i> Cancel
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $is_admin ? 7 : 5 ?>" class="text-center" style="padding: 4rem 2rem;">
                            <div
                                style="width: 64px; height: 64px; background-color: var(--slate-100); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: var(--slate-400); margin-bottom: 1.5rem;">
                                <i class="fa-regular fa-calendar-xmark" style="font-size: 2rem;"></i>
                            </div>
                            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">No Reservations Found</h3>
                            <p style="color: var(--slate-500);">There are no bookings to display at the moment.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require '../includes/footer.php'; ?>