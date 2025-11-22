<?php
require '../config.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$is_admin = ($_SESSION['role'] == 'admin');

// Fetch venues
$stmt = $pdo->query("SELECT v.*, b.Building_name FROM Venue v LEFT JOIN Building b ON v.Building_id = b.Building_id");
$venues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div class="page-title">
        <h1>Venues</h1>
        <p>Manage and book available spaces</p>
    </div>
    <?php if($is_admin): ?>
    <a href="add.php" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add Venue
    </a>
    <?php endif; ?>
</div>

<?php if(count($venues) > 0): ?>
<div class="grid grid-cols-3 gap-6">
    <?php foreach($venues as $venue): ?>
    <div class="venue-card">
        <div style="position: relative;">
            <?php if($venue['image_path']): ?>
                <img src="../<?= htmlspecialchars($venue['image_path']) ?>" alt="<?= htmlspecialchars($venue['venue_name']) ?>" class="venue-image">
            <?php else: ?>
                <div class="venue-image" style="display: flex; align-items: center; justify-content: center; color: var(--slate-300);">
                    <i class="fa-solid fa-image" style="font-size: 3rem;"></i>
                </div>
            <?php endif; ?>
            <div style="position: absolute; top: 1rem; right: 1rem;">
                <span class="badge badge-success" style="box-shadow: var(--shadow-md);">Available</span>
            </div>
        </div>
        <div class="venue-content">
            <div class="venue-meta">
                <i class="fa-regular fa-building"></i> <?= htmlspecialchars($venue['Building_name'] ?? 'Unknown Building') ?> • Floor <?= $venue['floor_number'] ?>
            </div>
            <h3 class="venue-title"><?= htmlspecialchars($venue['venue_name']) ?></h3>
            
            <div class="venue-footer">
                <?php if($is_admin): ?>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="edit.php?id=<?= $venue['venue_id'] ?>" class="btn btn-sm btn-outline">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <a href="delete.php?id=<?= $venue['venue_id'] ?>" class="btn btn-sm btn-outline" style="color: var(--danger-500); border-color: var(--danger-500);" onclick="return confirm('Are you sure? This will delete all associated reservations.')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                <?php endif; ?>
                <a href="../reservations/create.php?venue_id=<?= $venue['venue_id'] ?>" class="btn btn-sm btn-primary">
                    Book Now <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body text-center" style="padding: 4rem 2rem;">
        <div style="width: 64px; height: 64px; background-color: var(--slate-100); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: var(--slate-400); margin-bottom: 1.5rem;">
            <i class="fa-solid fa-building-circle-xmark" style="font-size: 2rem;"></i>
        </div>
        <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;">No Venues Found</h3>
        <p style="color: var(--slate-500); margin-bottom: 1.5rem;">Get started by adding your first venue.</p>
        <?php if($is_admin): ?>
        <a href="add.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add Venue
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
