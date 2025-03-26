<?php
include 'dbconnect.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fetch total employees
$sql_stats = "SELECT COUNT(*) AS total_employees FROM employee";
$result_stats = mysqli_query($conn, $sql_stats);
$total_employees = mysqli_fetch_assoc($result_stats)['total_employees'];

// Fetch total pending requests
$sql_pending_requests = "SELECT COUNT(*) AS total_pending_requests FROM leaverequest WHERE Status = 'Pending'";
$result_pending_requests = mysqli_query($conn, $sql_pending_requests);
$total_pending_requests = mysqli_fetch_assoc($result_pending_requests)['total_pending_requests'];

// Fetch total approved requests
$sql_approved_requests = "SELECT COUNT(*) AS total_approved_requests FROM leaverequest WHERE Status = 'Approved'";
$result_approved_requests = mysqli_query($conn, $sql_approved_requests);
$total_approved_requests = mysqli_fetch_assoc($result_approved_requests)['total_approved_requests'];

// Fetch total leave types
$sql_leave_types = "SELECT COUNT(*) AS total_leave_types FROM leavetypes";
$result_leave_types = mysqli_query($conn, $sql_leave_types);
$total_leave_types = mysqli_fetch_assoc($result_leave_types)['total_leave_types'];

// Fetch leave requests breakdown by type
$sql_leave_breakdown = "SELECT leave_type, COUNT(*) AS count FROM leaverequest GROUP BY leave_type";

$result_leave_breakdown = mysqli_query($conn, $sql_leave_breakdown);
$leave_breakdown = [];
while ($row = mysqli_fetch_assoc($result_leave_breakdown)) {
    $leave_breakdown[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="csss.css">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #2c3e50;
            padding-top: 20px;
            color: white;
        }

        .sidebar h2 {
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li a {
            display: block;
            color: white;
            padding: 12px;
            text-decoration: none;
        }

        .sidebar ul li a:hover {
            background: #1abc9c;
        }

        /* Main Content */
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        /* Stats Section */
        .stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 200px;
            text-align: center;
        }

        .stat-card h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .stat-card p {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
        }

        /* Leave Breakdown */
        .leave-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .leave-table th, .leave-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .leave-table th {
            background: #2c3e50;
            color: white;
        }

        .leave-table td {
            background: #ecf0f1;
        }

    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Dashboard</h2>
    <ul>
        <li><a href="admin.php">Admin panel</a></li>
        <li><a href="employee.php">Employees</a></li>
        <li><a href="leavetypes.php">Leave Types</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="auditlog.php">Audit Log</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <h1>Admin Dashboard</h1>

    <!-- Overview Stats -->
    <div class="stats">
        <div class="stat-card">
            <h3>Total Employees</h3>
            <p><?php echo htmlspecialchars($total_employees); ?></p>
        </div>
        <div class="stat-card">
            <h3>Pending Requests</h3>
            <p><?php echo htmlspecialchars($total_pending_requests); ?></p>
        </div>
        <div class="stat-card">
            <h3>Approved Requests</h3>
            <p><?php echo htmlspecialchars($total_approved_requests); ?></p>
        </div>
        <div class="stat-card">
            <h3>Leave Types</h3>
            <p><?php echo htmlspecialchars($total_leave_types); ?></p>
        </div>
    </div>

    <!-- Leave Breakdown Table -->
    <h2>Leave Requests Breakdown</h2>
    <table class="leave-table">
        <tr>
            <th>Leave Type</th>
            <th>Number of Requests</th>
        </tr>
        <?php foreach ($leave_breakdown as $leave): ?>
            <tr>
                <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                <td><?php echo htmlspecialchars($leave['count']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
