<?php
    include 'dbconnect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
?>

<?php

$sql_stats = "SELECT COUNT(*) AS total_employees FROM employee";
$result_stats = mysqli_query($conn, $sql_stats);
$total_employees = mysqli_fetch_assoc($result_stats)['total_employees'];

$sql_pending_requests = "SELECT COUNT(*) AS total_pending_requests FROM leaverequest WHERE Status = 'Pending'";
$result_pending_requests = mysqli_query($conn, $sql_pending_requests);
$total_pending_requests = mysqli_fetch_assoc($result_pending_requests)['total_pending_requests'];

$sql_approved_requests = "SELECT COUNT(*) AS total_approved_requests FROM leaverequest WHERE Status = 'Approved'";
$result_approved_requests = mysqli_query($conn, $sql_approved_requests);
$total_approved_requests = mysqli_fetch_assoc($result_approved_requests)['total_approved_requests'];

// Correct table name 'leavetypes' assumed
$sql_leave_types = "SELECT COUNT(*) AS total_leave_types FROM leavetypes";
$result_leave_types = mysqli_query($conn, $sql_leave_types);
$total_leave_types = mysqli_fetch_assoc($result_leave_types)['total_leave_types'];
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header>
        <h1>Dashboard</h1>
    </header>
    <nav>
        <ul>
        <li>
            <li><a href="admin.php">Admin</a></li>
            <li><a href="employee.php">Employees</a></li>
            <li><a href="leavetypes.php">Leave Types</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="auditlog.php">Audit Log</a></li>
            <li><a href="calendar.php">Calendar</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    
    <main>
        <section>
            <h2>Overview</h2>
            <div class="stats">
                <div>Total Employees: <?php echo htmlspecialchars($total_employees); ?></div>
                <div>Pending Requests: <?php echo htmlspecialchars($total_pending_requests); ?></div>
                <div>Approved Requests: <?php echo htmlspecialchars($total_approved_requests); ?></div>
                <div>Leave Types: <?php echo htmlspecialchars($total_leave_types); ?></div>
            </div>
        </section>
    </main>
</body>
</html>