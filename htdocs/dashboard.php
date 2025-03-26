<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'dbconnect.php'; 

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch statistics
$sql_stats = "SELECT COUNT(*) AS total_employees FROM employee";
$total_employees = $conn->query($sql_stats)->fetch_assoc()['total_employees'];

$sql_pending_requests = "SELECT COUNT(*) AS total_pending_requests FROM leaverequest WHERE Status = 'Pending'";
$total_pending_requests = $conn->query($sql_pending_requests)->fetch_assoc()['total_pending_requests'];

$sql_approved_requests = "SELECT COUNT(*) AS total_approved_requests FROM leaverequest WHERE Status = 'Approved'";
$total_approved_requests = $conn->query($sql_approved_requests)->fetch_assoc()['total_approved_requests'];

$sql_leave_types = "SELECT COUNT(*) AS total_leave_types FROM leavetypes";
$total_leave_types = $conn->query($sql_leave_types)->fetch_assoc()['total_leave_types'];

$sql_leave_breakdown = "SELECT lt.LeaveName, COUNT(lr.leave_type) AS count FROM leaverequest lr JOIN leavetypes lt ON lr.leave_type = lt.LeaveTypeID GROUP BY lt.LeaveName";
$result_leave_breakdown = $conn->query($sql_leave_breakdown);
$leave_breakdown = $result_leave_breakdown->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="bootstrap1.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4 fw-bold"> Dashboard</h2>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card shadow-sm p-3 mb-4 bg-white rounded">
                    <h5 class="fw-bold">Total Employees</h5>
                    <p class="fs-3 text-primary"> <?php echo htmlspecialchars($total_employees); ?> </p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-3 mb-4 bg-white rounded">
                    <h5 class="fw-bold">Pending Requests</h5>
                    <p class="fs-3 text-warning"> <?php echo htmlspecialchars($total_pending_requests); ?> </p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-3 mb-4 bg-white rounded">
                    <h5 class="fw-bold">Approved Requests</h5>
                    <p class="fs-3 text-success"> <?php echo htmlspecialchars($total_approved_requests); ?> </p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-3 mb-4 bg-white rounded">
                    <h5 class="fw-bold">Leave Types</h5>
                    <p class="fs-3 text-info"> <?php echo htmlspecialchars($total_leave_types); ?> </p>
                </div>
            </div>
        </div>

        <h3 class="mt-4 fw-bold">Leave Requests Breakdown</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Leave Type</th>
                        <th>Number of Requests</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_breakdown as $leave): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($leave['LeaveName']); ?></td>
                            <td><?php echo htmlspecialchars($leave['count']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="bootstrapbundle.js"></script>
</body>
</html>
