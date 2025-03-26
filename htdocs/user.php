<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'dbconnect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch leave balances
$sql_bal = "SELECT lt.LeaveTypeID, lt.LeaveName, 
                   COALESCE(lb.used_days, 0) AS used_days, 
                   COALESCE(lb.remaining_days, 0) AS remaining_days, 
                   COALESCE(lb.total_days, 0) AS total_days, 
                   COALESCE(SUM(CASE WHEN lr.Status = 'Pending' THEN lr.days_requested ELSE 0 END), 0) AS pending_days
            FROM leavetypes lt
            LEFT JOIN leave_balances lb 
                ON lt.LeaveTypeID = lb.leave_type AND lb.user_id = ?
            LEFT JOIN leaverequest lr 
                ON lb.user_id = lr.EmployeeID 
                AND lb.leave_type = lr.leave_type
            GROUP BY lt.LeaveTypeID, lt.LeaveName, lb.used_days, lb.remaining_days, lb.total_days";

$stmt_bal = $conn->prepare($sql_bal);
$stmt_bal->bind_param("i", $user_id);
$stmt_bal->execute();
$res_bal = $stmt_bal->get_result();

$balances = [];
while ($b = $res_bal->fetch_assoc()) {
    $balances[$b['LeaveTypeID']] = $b;
}
$stmt_bal->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="bootstrap1.css">
</head>
<body class="bg-light">
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="bg-dark text-white p-3 vh-100" style="width: 250px;">
            <h2 class="h5"> Dashboard</h2>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="apply_leave.php">Apply for Leave</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="leavehistory.php">Leave History</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="userprofile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="notifications.php">Notifications</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <div class="container p-4">
            <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>!</h1>
            <h2>Leave Balance</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Leave Type</th>
                            <th>Used Days</th>
                            <th>Remaining Days</th>
                            <th>Total Allocated</th>
                            <th>Pending Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($balances as $bal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($bal['LeaveName']); ?></td>
                                <td><?php echo (int)$bal['used_days']; ?></td>
                                <td><?php echo (int)$bal['remaining_days']; ?></td>
                                <td><?php echo (int)$bal['total_days']; ?></td>
                                <td><?php echo (int)$bal['pending_days']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <s<script src="bootstrap.bundle.min.js"></script>
</body>
</html>