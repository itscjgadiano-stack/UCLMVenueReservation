<?php
// config.php

/*
  This config supports two modes:
  - Oracle (PDO_OCI) when you set $use_oracle = true and fill in the
    connection variables below (username/password/tns).
  - SQLite fallback (default) which preserves the original behaviour.

  To enable Oracle:
  1. Set $use_oracle = true
  2. Replace YOUR_ORACLE_USER / YOUR_ORACLE_PASS with real credentials
  3. Adjust $tns if needed (e.g. //host:1521/servicename)

  Note: Ensure PHP has the PDO_OCI driver installed/enabled.
*/

try {
    $use_oracle = true; // <-- set to true to try Oracle first

    if ($use_oracle) {
        // Update these values for your Oracle connection
        $tns = "//localhost:1521/XEPDB1"; // example: //host:port/servicename
        $username = 'system';
        $password = 'chrystal09';

        // Try to create (connect to) Oracle via PDO. If this fails we'll
        // gracefully fall back to the original SQLite DB so the app stays up.
        try {
            $pdo = new PDO("oci:dbname={$tns}", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            $pdo->exec("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
            $pdo->exec("ALTER SESSION SET NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
            // Connected to Oracle successfully.
        } catch (PDOException $e) {
            // Log the oracle connection error and fall back to SQLite.
            error_log("Oracle connection failed: " . $e->getMessage());
            // Continue to SQLite fallback below.
            $pdo = null;
        }
    } else {
        $pdo = null;
    }

    // If Oracle wasn't selected or connection failed, use SQLite fallback
    if (!isset($pdo) || $pdo === null) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
        // Set errormode to exceptions
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Enable foreign keys constraints
        $pdo->exec("PRAGMA foreign_keys = ON");
    }

} catch (PDOException $e) {
    // If something unexpected happens creating SQLite, show error.
    die("Connection failed: " . $e->getMessage());
}
?>