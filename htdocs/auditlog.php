<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'dbconnect.php'; 

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch audit log entries with prepared statements
$query = "SELECT a.log_id, a.request_id, a.changed_by, a.change_message, a.timestamp, u.Name AS changed_by_name 
          FROM audit_log a 
          JOIN employee u ON a.changed_by = u.EmployeeID
          ORDER BY a.timestamp DESC";
$result = $conn->query($query);
$audit_logs = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="bootstrap1.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-history"></i> Audit Log</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Log ID</th>
                        <th>Request ID</th>
                        <th>Changed By</th>
                        <th>Change Message</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($audit_logs)): ?>
                        <?php foreach ($audit_logs as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['log_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['changed_by_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['change_message']); ?></td>
                                <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No audit logs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
