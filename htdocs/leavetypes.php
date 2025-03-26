<?php
include 'dbconnect.php';
session_start();

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch leave types
$sql = "SELECT * FROM leavetypes";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Types</title>
    <link rel="stylesheet" href="csss.css">
</head>
<body>

<div class="sidebar">
    <h2>Leave Types</h2>
    <ul>
        <li><a href="admin.php">Dashboard</a></li>
        <li><a href="employee.php">Employees</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="auditlog.php">Audit Log</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['LeaveType']) ?></td>
                            <td><?= htmlspecialchars($row['Description']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2">No leave types available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
