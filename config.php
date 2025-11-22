<?php
// config.php

try {
    // Create (connect to) SQLite database in file
    $pdo = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    
    // Set errormode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Enable foreign keys constraints
    $pdo->exec("PRAGMA foreign_keys = ON");
    
} catch(PDOException $e) {
    // Print error message
    die("Connection failed: " . $e->getMessage());
}
?>
