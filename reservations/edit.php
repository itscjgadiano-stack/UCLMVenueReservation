<?php
require '../config.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$reservation_id = $_GET['id'] ?? null;
$error = '';
$success = '';

if (!$reservation_id) {
    header("Location: list.php");
    exit;
}

// Fetch reservation details
$stmt = $pdo->prepare("SELECT r.*, v.venue_name, v.floor_number, rs.status_name 
                       FROM Reservation r 
                       JOIN Venue v ON r.venue_id = v.venue_id 
                       JOIN Reservation_Status rs ON r.status_id = rs.status_id 
                       WHERE r.reservation_id = ? AND r.user_id = ?");
$stmt->execute([$reservation_id, $_SESSION['user_id']]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    die("Reservation not found or access denied.");
}

// Prevent editing past reservations
if (strtotime($reservation['start_time']) < time()) {
    die("Cannot edit past reservations.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $reserved_by = trim($_POST['reserved_by']);
    
    if (empty($start_time) || empty($end_time) || empty($reserved_by)) {
        $error = "Please fill in all fields.";
    } elseif (strtotime($end_time) <= strtotime($start_time)) {
        $error = "End time must be after start time.";
    } else {
        // Check for conflicts (excluding current reservation)
        $stmt = $pdo->prepare("SELECT end_time FROM Reservation 
            WHERE venue_id = ? 
            AND reservation_id != ?
            AND status_id != (SELECT status_id FROM Reservation_Status WHERE status_name = 'Rejected')
            AND status_id != (SELECT status_id FROM Reservation_Status WHERE status_name = 'Cancelled')
            AND (
                (start_time < ? AND end_time > ?) OR
                (start_time < ? AND end_time > ?) OR
                (start_time >= ? AND end_time <= ?)
            )
            ORDER BY end_time DESC LIMIT 1");
        $stmt->execute([$reservation['venue_id'], $reservation_id, $end_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
        
        $conflict = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($conflict) {
            $suggested_time = date('h:i A', strtotime($conflict['end_time']));
            $error = "This venue is already booked for the selected time slot. Try rescheduling after <strong>$suggested_time</strong>.";
        } else {
            // Reset status to Pending if it was Approved
            $new_status_id = $reservation['status_id'];
            $status_message = "";
            
            if ($reservation['status_name'] == 'Approved') {
                $stmt = $pdo->query("SELECT status_id FROM Reservation_Status WHERE status_name = 'Pending'");
                $new_status_id = $stmt->fetchColumn();
                $status_message = " Since you modified an approved booking, it has been reset to Pending for re-approval.";
            }
            
            $stmt = $pdo->prepare("UPDATE Reservation SET start_time = ?, end_time = ?, reserved_by = ?, status_id = ? WHERE reservation_id = ?");
            if ($stmt->execute([$start_time, $end_time, $reserved_by, $new_status_id, $reservation_id])) {
                $success = "Reservation updated successfully!" . $status_message;
                // Refresh data
                $reservation['start_time'] = $start_time;
                $reservation['end_time'] = $end_time;
                $reservation['reserved_by'] = $reserved_by;
            } else {
                $error = "Something went wrong.";
            }
        }
    }
}
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="page-header">
        <div class="page-title">
            <h1>Edit Reservation</h1>
            <p>Reschedule or update details</p>
        </div>
        <a href="list.php" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div style="margin-bottom: 2rem; padding: 1.5rem; background-color: var(--primary-50); border-radius: var(--radius-lg); border: 1px solid var(--primary-100); display: flex; gap: 1rem; align-items: center;">
                <div style="width: 48px; height: 48px; background-color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-600); font-size: 1.25rem;">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0.25rem; color: var(--primary-700);"><?= htmlspecialchars($reservation['venue_name']) ?></h3>
                    <p style="color: var(--primary-600); font-size: 0.9rem;">Floor <?= $reservation['floor_number'] ?></p>
                </div>
            </div>

            <?php if($error): ?>
                <div style="background-color: #FEF2F2; color: #DC2626; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px solid #FECACA;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div style="background-color: #ECFDF5; color: #059669; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px solid #A7F3D0;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Event Name / Purpose</label>
                    <input type="text" name="reserved_by" class="form-control" required value="<?= htmlspecialchars($reservation['reserved_by']) ?>">
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime($reservation['start_time'])) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime($reservation['end_time'])) ?>">
                    </div>
                </div>

                <div class="text-center mt-4" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
