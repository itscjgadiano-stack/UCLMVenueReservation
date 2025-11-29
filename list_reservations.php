<?php
require 'config.php';
require 'includes/header.php';

// Check if user is logged in (header.php handles this now!)

?>
<div class="container" style="max-width: 1000px; margin: 2rem auto; padding: 0 1rem;">
    <div class="page-header">
        <h1>Latest Reservations</h1>
        <a href="reservations/create.php?venue_id=1" class="btn btn-primary">Create New</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Venue</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Join with Venue and User tables for better readability
                            // Note: Oracle tables might be case sensitive if created with quotes
                            // Adjust table names if needed based on your schema
                            $sql = "SELECT r.reservation_id, v.venue_name, u.user_name, r.reserved_by, 
                                           r.start_time, r.end_time, s.status_name
                                    FROM Reservation r
                                    JOIN Venue v ON r.venue_id = v.venue_id
                                    JOIN \"User\" u ON r.user_id = u.user_id
                                    JOIN Reservation_Status s ON r.status_id = s.status_id
                                    ORDER BY r.reservation_id DESC
                                    FETCH FIRST 10 ROWS ONLY"; // Oracle syntax
                        
                            $stmt = $pdo->query($sql);
                            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (empty($reservations)) {
                                echo "<tr><td colspan='7' class='text-center'>No reservations found.</td></tr>";
                            } else {
                                foreach ($reservations as $res) {
                                    $res = array_change_key_case($res, CASE_LOWER);
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($res['reservation_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($res['venue_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($res['user_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($res['reserved_by']) . "</td>";
                                    echo "<td>" . htmlspecialchars($res['start_time']) . "</td>";
                                    echo "<td>" . htmlspecialchars($res['end_time']) . "</td>";
                                    echo "<td><span class='badge badge-" . strtolower($res['status_name']) . "'>" . htmlspecialchars($res['status_name']) . "</span></td>";
                                    echo "</tr>";
                                }
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='7' class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>