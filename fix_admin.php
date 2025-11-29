<?php
require 'config.php';

echo "<h2>Admin User Diagnostic</h2>";

echo "<h3>1. Checking \"User\" table</h3>";
try {
    $stmt = $pdo->query('SELECT USER_ID, USER_NAME, ROLE, DEPARTMENT_ID FROM "User" WHERE ROLE = \'admin\'');
    $admins_user = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($admins_user)) {
        echo "<strong style='color: red;'>✗ No admin found in \"User\" table</strong><br>";
    } else {
        echo "<strong style='color: green;'>✓ Admin(s) found in \"User\" table:</strong><br>";
        echo "<table border='1'>";
        echo "<tr><th>USER_ID</th><th>USER_NAME</th><th>ROLE</th><th>DEPARTMENT_ID</th></tr>";
        foreach ($admins_user as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['USER_ID'] . "</td>";
            echo "<td>" . $admin['USER_NAME'] . "</td>";
            echo "<td>" . $admin['ROLE'] . "</td>";
            echo "<td>" . $admin['DEPARTMENT_ID'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>2. Checking APP_USER table</h3>";
try {
    $stmt = $pdo->query('SELECT USER_ID, USER_NAME, ROLE, DEPARTMENT_ID FROM APP_USER WHERE ROLE = \'admin\'');
    $admins_app = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($admins_app)) {
        echo "<strong style='color: red;'>✗ No admin found in APP_USER table</strong><br>";
    } else {
        echo "<strong style='color: green;'>✓ Admin(s) found in APP_USER table:</strong><br>";
        echo "<table border='1'>";
        echo "<tr><th>USER_ID</th><th>USER_NAME</th><th>ROLE</th><th>DEPARTMENT_ID</th></tr>";
        foreach ($admins_app as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['USER_ID'] . "</td>";
            echo "<td>" . $admin['USER_NAME'] . "</td>";
            echo "<td>" . $admin['ROLE'] . "</td>";
            echo "<td>" . $admin['DEPARTMENT_ID'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>3. Testing Password Hash</h3>";
$test_password = "admin123";
$test_hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "Test password: <code>$test_password</code><br>";
echo "Test hash: <code>$test_hash</code><br>";
echo "Verify test: " . (password_verify($test_password, $test_hash) ? "✓ PASS" : "✗ FAIL") . "<br>";

echo "<br><h3>4. Checking Admin Password Hash</h3>";
try {
    $stmt = $pdo->query('SELECT USER_NAME, PASSWORD_HASH FROM "User" WHERE ROLE = \'admin\' FETCH FIRST 1 ROWS ONLY');
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "Username: " . $admin['USER_NAME'] . "<br>";
        echo "Password Hash: <code>" . substr($admin['PASSWORD_HASH'], 0, 50) . "...</code><br>";

        // Test common passwords
        $common_passwords = ['admin', 'admin123', 'password', '123456'];
        echo "<br>Testing common passwords:<br>";
        foreach ($common_passwords as $pwd) {
            $result = password_verify($pwd, $admin['PASSWORD_HASH']);
            echo "- '$pwd': " . ($result ? "<strong style='color: green;'>✓ MATCH</strong>" : "✗ No match") . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>5. Create/Reset Admin User</h3>";
echo "<form method='POST'>";
echo "<p>Click below to create or reset the admin user:</p>";
echo "<button type='submit' name='create_admin' style='padding: 10px 20px; background: #4F46E5; color: white; border: none; border-radius: 5px; cursor: pointer;'>Create/Reset Admin User</button>";
echo "</form>";

if (isset($_POST['create_admin'])) {
    try {
        $admin_username = 'admin';
        $admin_password = 'admin123';
        $admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        $admin_role = 'admin';
        $admin_dept = 1;

        // Check if admin exists in "User"
        $stmt = $pdo->prepare('SELECT user_id FROM "User" WHERE user_name = ?');
        $stmt->execute([$admin_username]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $existing = array_change_key_case($existing, CASE_LOWER);
            $admin_id = $existing['user_id'];

            // Update existing admin
            $stmt = $pdo->prepare('UPDATE "User" SET password_hash = ?, role = ?, department_id = ? WHERE user_id = ?');
            $stmt->execute([$admin_hash, $admin_role, $admin_dept, $admin_id]);
            echo "<p style='color: green;'>✓ Updated admin user in \"User\" table</p>";

            // Update in APP_USER
            $stmt = $pdo->prepare('SELECT user_id FROM APP_USER WHERE user_id = ?');
            $stmt->execute([$admin_id]);
            if ($stmt->fetch()) {
                $stmt = $pdo->prepare('UPDATE APP_USER SET password_hash = ?, role = ?, department_id = ? WHERE user_id = ?');
                $stmt->execute([$admin_hash, $admin_role, $admin_dept, $admin_id]);
                echo "<p style='color: green;'>✓ Updated admin user in APP_USER table</p>";
            } else {
                $stmt = $pdo->prepare('INSERT INTO APP_USER (user_id, user_name, password_hash, role, department_id) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$admin_id, $admin_username, $admin_hash, $admin_role, $admin_dept]);
                echo "<p style='color: green;'>✓ Created admin user in APP_USER table</p>";
            }
        } else {
            // Create new admin
            $stmt = $pdo->prepare('INSERT INTO "User" (user_name, password_hash, role, department_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin_username, $admin_hash, $admin_role, $admin_dept]);

            $stmt = $pdo->prepare('SELECT user_id FROM "User" WHERE user_name = ?');
            $stmt->execute([$admin_username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $result = array_change_key_case($result, CASE_LOWER);
            $admin_id = $result['user_id'];

            echo "<p style='color: green;'>✓ Created admin user in \"User\" table (ID: $admin_id)</p>";

            // Create in APP_USER
            $stmt = $pdo->prepare('INSERT INTO APP_USER (user_id, user_name, password_hash, role, department_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$admin_id, $admin_username, $admin_hash, $admin_role, $admin_dept]);
            echo "<p style='color: green;'>✓ Created admin user in APP_USER table</p>";
        }

        echo "<br><strong style='color: green; font-size: 1.2em;'>✓ Admin user ready!</strong><br>";
        echo "<p><strong>Username:</strong> admin<br>";
        echo "<strong>Password:</strong> admin123</p>";
        echo "<p><a href='auth/login.php' style='color: #4F46E5; text-decoration: underline;'>Go to Login Page</a></p>";

    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
}
?>