<?php
// setup_database.php
require 'config.php';

try {
    $commands = [
        "CREATE TABLE IF NOT EXISTS Building (
            Building_id INTEGER PRIMARY KEY AUTOINCREMENT,
            Building_name TEXT NOT NULL UNIQUE
        )",
        "CREATE TABLE IF NOT EXISTS Department (
            department_id INTEGER PRIMARY KEY AUTOINCREMENT,
            department_name TEXT NOT NULL UNIQUE
        )",
        "CREATE TABLE IF NOT EXISTS User (
            user_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_name TEXT NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL,
            department_id INTEGER,
            FOREIGN KEY (department_id) REFERENCES Department(department_id)
        )",
        "CREATE TABLE IF NOT EXISTS Venue (
            venue_id INTEGER PRIMARY KEY AUTOINCREMENT,
            venue_name TEXT NOT NULL UNIQUE,
            floor_number INTEGER NOT NULL,
            image_path TEXT,
            Building_id INTEGER,
            FOREIGN KEY (Building_id) REFERENCES Building(Building_id)
        )",
        "CREATE TABLE IF NOT EXISTS Reservation_Status (
            status_id INTEGER PRIMARY KEY AUTOINCREMENT,
            status_name TEXT NOT NULL UNIQUE
        )",
        "CREATE TABLE IF NOT EXISTS Reservation (
            reservation_id INTEGER PRIMARY KEY AUTOINCREMENT,
            reserved_by TEXT NOT NULL,
            start_time DATETIME NOT NULL,
            end_time DATETIME NOT NULL,
            venue_id INTEGER,
            user_id INTEGER,
            status_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (venue_id) REFERENCES Venue(venue_id),
            FOREIGN KEY (user_id) REFERENCES User(user_id),
            FOREIGN KEY (status_id) REFERENCES Reservation_Status(status_id)
        )",
        "CREATE TABLE IF NOT EXISTS Equipment (
            equipment_id INTEGER PRIMARY KEY AUTOINCREMENT,
            equipment_name TEXT NOT NULL UNIQUE,
            quantity INTEGER NOT NULL,
            description TEXT,
            venue_id INTEGER,
            FOREIGN KEY (venue_id) REFERENCES Venue(venue_id)
        )",
        "CREATE TABLE IF NOT EXISTS Notification (
            notification_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            message TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            is_read TEXT DEFAULT 'N',
            FOREIGN KEY (user_id) REFERENCES User(user_id)
        )"
    ];

    foreach ($commands as $command) {
        $pdo->exec($command);
    }

    // Seed initial data
    $pdo->exec("INSERT OR IGNORE INTO Reservation_Status (status_name) VALUES ('Pending'), ('Approved'), ('Rejected'), ('Cancelled')");
    
    $departments = [
        'AAC', 'ACCOUNTING', 'CAD', 'CARES', 'CASHIER', 'CCS', 'CDRC', 'CHTM', 
        'CLINIC', 'CRIMINOLOGY', 'CTE', 'EDP', 'ERS', 'GUIDANCE', 'HR', 'IQA', 
        'LIBRARY', 'MARE', 'MDO', 'MT', 'NSA', 'NURSING', 'OTO', 'PCO', 
        'REGISTRAR', 'SAO', 'SCHOLARSHIP', 'TETAC', 'URO'
    ];
    
    $dept_sql = "INSERT OR IGNORE INTO Department (department_name) VALUES ";
    $values = [];
    foreach ($departments as $dept) {
        $values[] = "('$dept')";
    }
    $dept_sql .= implode(", ", $values);
    $pdo->exec($dept_sql);
    // Default admin user (password: admin123)
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT OR IGNORE INTO User (user_name, password_hash, role, department_id) VALUES ('admin', '$password', 'admin', 1)");

    echo "Database tables created and seeded successfully.";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
