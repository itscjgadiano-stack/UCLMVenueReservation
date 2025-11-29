<?php
require '../config.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->query('SELECT u.*, d.department_name FROM "User" u LEFT JOIN Department d ON u.department_id = d.department_id');
$rawUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Normalize keys to lowercase for consistent template access across drivers
$users = array_map(function($u) {
    return array_change_key_case($u, CASE_LOWER);
}, $rawUsers);
?>

<div class="page-header">
    <div class="page-title">
        <h1>Users</h1>
        <p>Manage registered users</p>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Department</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($users) > 0): ?>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><span style="font-family: monospace; color: var(--slate-500);">#<?= $user['user_id'] ?></span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 32px; height: 32px; background-color: var(--slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: var(--slate-600);">
                                    <?= strtoupper(substr($user['user_name'], 0, 1)) ?>
                                </div>
                                <div style="font-weight: 600; color: var(--slate-900);"><?= htmlspecialchars($user['user_name']) ?></div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $user['role'] == 'admin' ? 'badge-primary' : 'badge-success' ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--slate-600);">
                                <i class="fa-regular fa-building"></i>
                                <?= htmlspecialchars($user['department_name'] ?? 'N/A') ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 4rem 2rem;">
                            <div style="width: 64px; height: 64px; background-color: var(--slate-100); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: var(--slate-400); margin-bottom: 1.5rem;">
                                <i class="fa-solid fa-users-slash" style="font-size: 2rem;"></i>
                            </div>
                            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">No Users Found</h3>
                            <p style="color: var(--slate-500);">No registered users in the system.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
