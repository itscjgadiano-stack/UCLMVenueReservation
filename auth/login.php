<?php
require '../config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare('SELECT * FROM "User" WHERE user_name = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            // Normalize column keys to lowercase for consistent access across drivers
            $user = array_change_key_case($user, CASE_LOWER);
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['department_id'] = $user['department_id'];
            
            header("Location: ../index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UCLM Venue Reservation</title>
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
        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--primary-600), var(--primary-500)); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
            <i class="fa-solid fa-building-columns"></i>
        </div>
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Welcome Back</h1>
        <p style="color: var(--slate-500);">Sign in to your account to continue</p>
    </div>

    <?php if($error): ?>
        <div style="background-color: #FEF2F2; color: #DC2626; padding: 0.875rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px solid #FECACA; font-size: 0.9rem; display: flex; gap: 0.5rem; align-items: center;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label">Username</label>
            <div style="position: relative;">
                <i class="fa-solid fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--slate-400);"></i>
                <input type="text" name="username" class="form-control" style="padding-left: 2.75rem;" placeholder="Enter your username" required>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Password</label>
            <div style="position: relative;">
                <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--slate-400);"></i>
                <input type="password" name="password" class="form-control" style="padding-left: 2.75rem;" placeholder="Enter your password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.875rem;">
            Sign In <i class="fa-solid fa-arrow-right" style="margin-left: 0.5rem;"></i>
        </button>
    </form>

    <div class="text-center mt-4" style="border-top: 1px solid var(--slate-100); padding-top: 1.5rem;">
        <p style="font-size: 0.9rem; color: var(--slate-500);">
            Don't have an account? <a href="register.php" style="color: var(--primary-600); font-weight: 600;">Sign up</a>
        </p>
    </div>
</div>

</body>
</html>
