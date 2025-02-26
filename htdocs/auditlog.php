<?php
include 'dbconnect.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch audit log entries
$sql = "SELECT a.log_id, a.request_id, a.changed_by, a.change_message, a.timestamp, u.name as changed_by_name 
        FROM audit_log a 
        JOIN users u ON a.changed_by = u.id
        ORDER BY a.timestamp DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header>
        <h1>Audit Log</h1>
    </header>
    <nav>
        <ul>
            <li><a href="admin.php">Admin</a></li>
            <li> <a href="dashboard.php">Dashboard</a></li>
            <li><a href="employee.php">Employees</a></li>
            <li><a href="leavetypes.php">Leave Types</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="calendar.php">Calendar</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
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
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['log_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['changed_by_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['change_message']); ?></td>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                        </tr>
                    <?php endwhile; ?>
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
