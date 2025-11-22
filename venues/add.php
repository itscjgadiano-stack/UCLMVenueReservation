<?php
require '../config.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$error = '';
$success = '';

// Fetch buildings
$stmt = $pdo->query("SELECT * FROM Building");
$buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $venue_name = trim($_POST['venue_name']);
    $floor_number = $_POST['floor_number'];
    $building_id = $_POST['building_id'];
    
    // Handle Image Upload
    $image_path = null;
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
            $stmt = $pdo->prepare("INSERT INTO Venue (venue_name, floor_number, Building_id, image_path) VALUES (?, ?, ?, ?)");
            $stmt->execute([$venue_name, $floor_number, $building_id, $image_path]);
            $success = "Venue added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding venue: " . $e->getMessage();
        }
    }
}
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="page-header">
        <div class="page-title">
            <h1>Add New Venue</h1>
            <p>Create a new space for reservations</p>
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
                    <input type="text" name="venue_name" class="form-control" placeholder="e.g. Conference Room A" required>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Building</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select name="building_id" class="form-control" required>
                                <option value="">Select Building</option>
                                <?php foreach($buildings as $building): ?>
                                    <option value="<?= $building['Building_id'] ?>"><?= htmlspecialchars($building['Building_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-outline" onclick="document.getElementById('addBuildingModal').style.display='flex'" title="Add Building">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Floor Number</label>
                        <input type="number" name="floor_number" class="form-control" placeholder="e.g. 2" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Venue Image</label>
                    <div style="border: 2px dashed var(--slate-300); padding: 2rem; border-radius: var(--radius-lg); text-align: center; background-color: var(--slate-50); cursor: pointer;" onclick="document.getElementById('fileInput').click()">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2rem; color: var(--slate-400); margin-bottom: 1rem;"></i>
                        <p style="color: var(--slate-500); margin-bottom: 0.5rem;">Click to upload image</p>
                        <input type="file" id="fileInput" name="image" class="form-control" accept="image/*" style="display: none;">
                    </div>
                </div>

                <div class="text-center mt-4" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fa-solid fa-save"></i> Save Venue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Simple Modal for Adding Building -->
<div id="addBuildingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: white; width: 100%; max-width: 400px; padding: 2rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl);">
        <h3 style="margin-bottom: 1.5rem;">Add New Building</h3>
        <form method="POST" action="add_building.php">
            <div class="form-group">
                <label class="form-label">Building Name</label>
                <input type="text" name="building_name" class="form-control" required>
            </div>
            <div class="flex justify-between mt-4">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addBuildingModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Building</button>
            </div>
        </form>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
