<?php
include 'dbconnect.php';
session_start();

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch audit log entries with prepared statements
$query = "SELECT a.log_id, a.request_id, a.changed_by, a.change_message, a.timestamp, u.name AS changed_by_name 
          FROM audit_log a 
          JOIN users u ON a.changed_by = u.id
          ORDER BY a.timestamp DESC";
$result = mysqli_query($conn, $query);
$audit_logs = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log</title>
    <link rel="stylesheet" href="csss.css">
</head>
<body>

<div class="sidebar">
    <h2>Audit Log</h2>
    <ul>
        <li><a href="admin.php">Admin Panel</a></li>
        <li><a href="employee.php">Employees</a></li>
        <li><a href="leavetypes.php">Leave Types</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

    <div class="container">
        <table>
            <thead>
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
                        <td colspan="5">No audit logs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
