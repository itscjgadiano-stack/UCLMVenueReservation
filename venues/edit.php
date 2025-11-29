<?php
require '../config.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: list.php");
    exit;
}

// Fetch venue
$stmt = $pdo->prepare("SELECT * FROM Venue WHERE venue_id = ?");
$stmt->execute([$id]);
$venue = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venue) {
    die("Venue not found.");
}

// Normalize keys to lowercase so templates work the same for SQLite and Oracle
$venue = array_change_key_case($venue, CASE_LOWER);

// Fetch buildings
$stmt = $pdo->query("SELECT * FROM Building");
$rawBuildings = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Normalize building keys to lowercase as well
$buildings = array_map(function($b){ return array_change_key_case($b, CASE_LOWER); }, $rawBuildings);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $venue_name = trim($_POST['venue_name']);
    $floor_number = $_POST['floor_number'];
    $building_id = $_POST['building_id'];
    
    // Handle Image Upload
    $image_path = $venue['image_path']; // Default to existing
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "assets/uploads/" . $new_filename;
        }
    }

    if (empty($venue_name) || empty($floor_number) || empty($building_id)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE Venue SET venue_name = ?, floor_number = ?, Building_id = ?, image_path = ? WHERE venue_id = ?");
            $stmt->execute([$venue_name, $floor_number, $building_id, $image_path, $id]);
            $success = "Venue updated successfully!";
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM Venue WHERE venue_id = ?");
            $stmt->execute([$id]);
            $venue = $stmt->fetch(PDO::FETCH_ASSOC);
            $venue = array_change_key_case($venue, CASE_LOWER);
        } catch (PDOException $e) {
            $error = "Error updating venue: " . $e->getMessage();
        }
    }
}
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="page-header">
        <div class="page-title">
            <h1>Edit Venue</h1>
            <p>Update venue details</p>
        </div>
        <a href="list.php" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if($error): ?>
                <div style="background-color: #FEF2F2; color: #DC2626; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px solid #FECACA;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div style="background-color: #ECFDF5; color: #059669; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px solid #A7F3D0;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Venue Name</label>
                    <input type="text" name="venue_name" class="form-control" value="<?= htmlspecialchars($venue['venue_name']) ?>" required>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Building</label>
                        <select name="building_id" class="form-control" required>
                            <option value="">Select Building</option>
                                    <?php foreach($buildings as $building): ?>
                                        <option value="<?= $building['building_id'] ?>" <?= $building['building_id'] == $venue['building_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($building['building_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Floor Number</label>
                        <input type="number" name="floor_number" class="form-control" value="<?= htmlspecialchars($venue['floor_number']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Venue Image</label>
                    <?php if($venue['image_path']): ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="../<?= htmlspecialchars($venue['image_path']) ?>" alt="Current Image" style="height: 150px; border-radius: var(--radius-md); object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <div style="border: 2px dashed var(--slate-300); padding: 2rem; border-radius: var(--radius-lg); text-align: center; background-color: var(--slate-50); cursor: pointer;" onclick="document.getElementById('fileInput').click()">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2rem; color: var(--slate-400); margin-bottom: 1rem;"></i>
                        <p style="color: var(--slate-500); margin-bottom: 0.5rem;">Click to upload new image</p>
                        <input type="file" id="fileInput" name="image" class="form-control" accept="image/*" style="display: none;">
                    </div>
                </div>

                <div class="text-center mt-4" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fa-solid fa-save"></i> Update Venue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
