<?php
require 'config.php';

echo "<h2>Foreign Key Analysis</h2>";

// Get the referenced constraint details
try {
    echo "<h3>FK_RESERVATION_USER Details</h3>";
    $stmt = $pdo->query("
        SELECT 
            a.constraint_name, 
            a.table_name,
            a.column_name,
            c_pk.table_name r_table_name,
            c_pk.constraint_name r_pk
        FROM user_cons_columns a
        JOIN user_constraints c ON a.constraint_name = c.constraint_name
        JOIN user_constraints c_pk ON c.r_constraint_name = c_pk.constraint_name
        WHERE c.constraint_type = 'R'
        AND a.constraint_name = 'FK_RESERVATION_USER'
    ");

    $fk_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>FK Name</th><th>Table</th><th>Column</th><th>References Table</th><th>References PK</th></tr>";
    foreach ($fk_details as $row) {
        echo "<tr>";
        echo "<td>" . $row['CONSTRAINT_NAME'] . "</td>";
        echo "<td>" . $row['TABLE_NAME'] . "</td>";
        echo "<td>" . $row['COLUMN_NAME'] . "</td>";
        echo "<td>" . $row['R_TABLE_NAME'] . "</td>";
        echo "<td>" . $row['R_PK'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Now check what the referenced table actually has
    if (!empty($fk_details)) {
        $ref_table = $fk_details[0]['R_TABLE_NAME'];
        $ref_column = $fk_details[0]['COLUMN_NAME']; // This is the column in RESERVATION table

        echo "<h3>Values in Referenced Table: $ref_table</h3>";

        // Handle case-sensitive table names
        $table_query = $ref_table;
        if ($ref_table === 'User') {
            $table_query = '"User"';
        }

        $stmt = $pdo->query("SELECT * FROM $table_query");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<table border='1'>";
        if (!empty($records)) {
            echo "<tr>";
            foreach (array_keys($records[0]) as $col) {
                echo "<th>$col</th>";
            }
            echo "</tr>";

            foreach ($records as $record) {
                echo "<tr>";
                foreach ($record as $val) {
                    echo "<td>$val</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

// Also check the actual User table primary key
echo "<h3>User Table Primary Key</h3>";
try {
    $stmt = $pdo->query("
        SELECT cols.column_name
        FROM user_constraints cons
        JOIN user_cons_columns cols ON cons.constraint_name = cols.constraint_name
        WHERE cons.table_name = 'User'
        AND cons.constraint_type = 'P'
    ");

    $pk_cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($pk_cols);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>