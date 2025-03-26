<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'dbconnect.php';

// 1. Check user session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}



$user_id = $_SESSION['user_id'];

// 2. Fetch user info from `users`
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
$user = $res_user->fetch_assoc();
$stmt_user->close();

// 3. Ensure there's an `employee` record for position & department
$sql_emp = "SELECT * FROM employee WHERE EmployeeID = ?";
$stmt_emp = $conn->prepare($sql_emp);
$stmt_emp->bind_param("i", $user_id);
$stmt_emp->execute();
$res_emp = $stmt_emp->get_result();
$employee = $res_emp->fetch_assoc();
$stmt_emp->close();

// If no employee record, create one
if (!$employee) {
    $empInsert = "INSERT INTO employee (EmployeeID, name, email, position, department, Hire_Date)
                  VALUES (?, ?, ?, '', '', NOW())";
    $stmt_ins = $conn->prepare($empInsert);
    $stmt_ins->bind_param("iss", $user_id, $user['name'], $user['email']);
    $stmt_ins->execute();
    $stmt_ins->close();
}

// 4. Fetch all leave types from `leavetypes`
$sql_lt = "SELECT LeaveTypeID, LeaveType FROM leavetypes";
$res_lt = $conn->query($sql_lt);

$leave_types = [];
while ($lt = $res_lt->fetch_assoc()) {
    $leave_types[$lt['LeaveTypeID']] = $lt['LeaveType'];
}

// 5. Fetch the user's current leave balances
$sql_bal = "SELECT leave_type, used_days, remaining_days, total_days FROM leave_balances WHERE user_id = ?";
$stmt_bal = $conn->prepare($sql_bal);
$stmt_bal->bind_param("i", $user_id);
$stmt_bal->execute();
$res_bal = $stmt_bal->get_result();

$balances = [];
while ($b = $res_bal->fetch_assoc()) {
    $balances[$b['leave_type']] = $b;
}
$stmt_bal->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header>
        <h1>User Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($user['name'] ?? ''); ?>!</p>
    </header>
    <nav>
        <ul>
            <li><a href="apply_leave.php">Apply for Leave</a></li>
            <li><a href="leavehistory.php">Leave History</a></li>
            <li><a href="userprofile.php">Profile</a></li>
            <li><a href="notifications.php">Notification</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <br>
    
    <div class="form-container">
        <h2>Your Leave Balances</h2>
        
        <?php if (!empty($balances)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Used Days</th>
                        <th>Remaining Days</th>
                        <th>Total Allocated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($balances as $leave_id => $bal): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($leave_types[$leave_id] ?? 'Unknown'); ?></td>
                            <td><?php echo (int)$bal['used_days']; ?></td>
                            <td><?php echo (int)$bal['remaining_days']; ?></td>
                            <td><?php echo (int)$bal['total_days']; ?></td>
                            <td class="actions">
                                <a href="apply_leave.php?type=<?php echo $leave_id; ?>">Apply</a>
                                <a href="leavehistory.php?filter=<?php echo urlencode($leave_types[$leave_id] ?? 'Unknown'); ?>">History</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No leave balances available.</p>
        <?php endif; ?>
    </div>
</body>
</html>