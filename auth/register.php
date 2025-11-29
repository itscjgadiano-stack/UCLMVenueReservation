<?php
require '../config.php';
session_start();

$error = '';
$success = '';

// Fetch departments for dropdown
$stmt = $pdo->query("SELECT * FROM Department");
$rawDepartments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Normalize department result keys to lowercase so code works with both
// SQLite (lowercase column names) and Oracle (uppercase column names).
$departments = array_map(function ($d) {
    $row = [];
    foreach ($d as $k => $v) {
        $row[strtolower($k)] = $v;
    }
    return $row;
}, $rawDepartments);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department_id = $_POST['department_id'];

    if (empty($username) || empty($password) || empty($department_id)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username exists (use quoted name to match Oracle-created table)
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM "User" WHERE user_name = ?');
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username already taken.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // Default role

            // Use explicit quoted table name if necessary. For Oracle the table was
            // created as "User" (quoted), so keep the same name here.
            $stmt = $pdo->prepare('INSERT INTO "User" (user_name, password_hash, role, department_id) VALUES (?, ?, ?, ?)');
            if ($stmt->execute([$username, $password_hash, $role, $department_id])) {
                // Auto-login: retrieve the inserted user_id. PDO::lastInsertId() is
                // not reliable with Oracle IDENTITY; select by username instead.
                $idStmt = $pdo->prepare('SELECT user_id FROM "User" WHERE user_name = ?');
                $idStmt->execute([$username]);
                $newId = $idStmt->fetchColumn();

                // IMPORTANT: Also insert into APP_USER table for Oracle FK compatibility
                try {
                    $appUserStmt = $pdo->prepare('INSERT INTO APP_USER (user_id, user_name, password_hash, role, department_id) VALUES (?, ?, ?, ?, ?)');
                    $appUserStmt->execute([$newId, $username, $password_hash, $role, $department_id]);
                } catch (PDOException $e) {
                    // If APP_USER insert fails (e.g., duplicate), log but continue
                    error_log("Failed to insert into APP_USER: " . $e->getMessage());
                }

                $_SESSION['user_id'] = $newId;
                $_SESSION['user_name'] = $username;
                $_SESSION['role'] = $role;
                $_SESSION['department_id'] = $department_id;

                header("Location: ../index.php");
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UCLM Venue Reservation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #F3F4F6;
        }

        .auth-card {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: var(--text-light);
            font-size: 0.875rem;
        }
    </style>
</head>

<body class="auth-layout">

    <div class="auth-card">
        <div class="text-center mb-4">
            <div
                style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--primary-600), var(--primary-500)); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Create Account</h1>
            <p style="color: var(--slate-500);">Join to start booking venues</p>
        </div>

        <?php if ($error): ?>
            <div
                style="background-color: #FEF2F2; color: #DC2626; padding: 0.875rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px solid #FECACA; font-size: 0.9rem; display: flex; gap: 0.5rem; align-items: center;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div
                style="background-color: #ECFDF5; color: #059669; padding: 0.875rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px solid #A7F3D0; font-size: 0.9rem; display: flex; gap: 0.5rem; align-items: center;">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <?= htmlspecialchars($success) ?> <a href="login.php"
                        style="text-decoration: underline; font-weight: 600; color: #047857;">Login here</a>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
            </div>

            <div class="form-group">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-control" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"
                style="width: 100%; justify-content: center; padding: 0.875rem;">
                Create Account
            </button>
        </form>

        <div class="text-center mt-4" style="border-top: 1px solid var(--slate-100); padding-top: 1.5rem;">
            <p style="font-size: 0.9rem; color: var(--slate-500);">
                Already have an account? <a href="login.php" style="color: var(--primary-600); font-weight: 600;">Sign
                    in</a>
            </p>
        </div>
    </div>

</body>

</html>