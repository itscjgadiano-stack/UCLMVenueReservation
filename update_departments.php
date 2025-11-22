<?php
require 'config.php';

try {
    $new_departments = [
        'AAC', 'ACCOUNTING', 'CAD', 'CARES', 'CASHIER', 'CCS', 'CDRC', 'CHTM', 
        'CLINIC', 'CRIMINOLOGY', 'CTE', 'EDP', 'ERS', 'GUIDANCE', 'HR', 'IQA', 
        'LIBRARY', 'MARE', 'MDO', 'MT', 'NSA', 'NURSING', 'OTO', 'PCO', 
        'REGISTRAR', 'SAO', 'SCHOLARSHIP', 'TETAC', 'URO'
    ];

    // 1. Insert new departments
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO Department (department_name) VALUES (?)");
    foreach ($new_departments as $dept) {
        $stmt->execute([$dept]);
    }
    echo "New departments inserted.\n";

    // 2. Reassign users from obsolete departments (IT, Admin) to 'EDP' or 'HR'
    // Let's find the ID of 'EDP' to use as a safe fallback for IT/Admin users
    $stmt = $pdo->prepare("SELECT department_id FROM Department WHERE department_name = ?");
    $stmt->execute(['EDP']);
    $edp_id = $stmt->fetchColumn();

    if ($edp_id) {
        // Find IDs of obsolete departments that we want to remove if they exist
        // The original list was IT, HR, Admin. HR is in the new list. IT and Admin are not.
        $obsolete = ['IT', 'Admin'];
        
        foreach ($obsolete as $obs_name) {
            $stmt = $pdo->prepare("SELECT department_id FROM Department WHERE department_name = ?");
            $stmt->execute([$obs_name]);
            $obs_id = $stmt->fetchColumn();

            if ($obs_id) {
                // Move users to EDP
                $update = $pdo->prepare("UPDATE User SET department_id = ? WHERE department_id = ?");
                $update->execute([$edp_id, $obs_id]);
                echo "Moved users from $obs_name to EDP.\n";

                // Now delete the department
                $del = $pdo->prepare("DELETE FROM Department WHERE department_id = ?");
                $del->execute([$obs_id]);
                echo "Deleted obsolete department: $obs_name.\n";
            }
        }
    }

    // 3. Verify list
    $stmt = $pdo->query("SELECT department_name FROM Department ORDER BY department_name");
    $current_depts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current Departments:\n" . implode(", ", $current_depts) . "\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
