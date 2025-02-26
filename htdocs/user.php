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

// If no employee record, create one (adjust columns if needed)
if (!$employee) {
    $empInsert = "INSERT INTO employee (EmployeeID, name, email, position, department, Hire_Date)
                  VALUES (?, ?, ?, '', '', NOW())";
    $stmt_ins = $conn->prepare($empInsert);
    $stmt_ins->bind_param("iss", $user_id, $user['name'], $user['email']);
    $stmt_ins->execute();
    $stmt_ins->close();

    // Re-fetch
    $stmt_emp = $conn->prepare($sql_emp);
    $stmt_emp->bind_param("i", $user_id);
    $stmt_emp->execute();
    $res_emp = $stmt_emp->get_result();
    $employee = $res_emp->fetch_assoc();
    $stmt_emp->close();
}

// 4. Fetch all leave types from `leavetypes`
$sql_lt = "SELECT LeaveTypeID, LeaveType, Description FROM leavetypes";
$res_lt = $conn->query($sql_lt);

$leave_types = [];
while ($lt = $res_lt->fetch_assoc()) {
    $leave_id   = (int)$lt['LeaveTypeID'];
    $leave_name = $lt['LeaveType'];      // e.g. "Vacation Leave"
    $desc       = $lt['Description'];    // e.g. "For personal time..."

    // 4a. Check `leave_policy` for allowed_days
    $pos = $employee['position']   ?? '';
    $dep = $employee['department'] ?? '';
    $sql_pol = "SELECT allowed_days FROM leave_policy
                WHERE position = ?
                  AND department = ?
                  AND leave_type = ?";
    $stmt_pol = $conn->prepare($sql_pol);
    $stmt_pol->bind_param("ssi", $pos, $dep, $leave_id);
    $stmt_pol->execute();
    $res_pol = $stmt_pol->get_result();
    $pol = $res_pol->fetch_assoc();
    $stmt_pol->close();

    // If no policy row, fallback to 0
    $allowed = $pol ? (int)$pol['allowed_days'] : 0;

    $leave_types[$leave_id] = [
        'name'        => $leave_name,
        'description' => $desc,
        'default_days'=> $allowed
    ];
}

// 5. Fetch the user's current leave balances
$sql_bal = "SELECT leave_type, used_days, remaining_days, total_days
            FROM leave_balances
            WHERE user_id = ?";
$stmt_bal = $conn->prepare($sql_bal);
$stmt_bal->bind_param("i", $user_id);
$stmt_bal->execute();
$res_bal = $stmt_bal->get_result();

$balances = [];
while ($b = $res_bal->fetch_assoc()) {
    $balances[$b['leave_type']] = $b;
}
$stmt_bal->close();

// 6. Insert missing rows in `leave_balances` for each leave type
$sql_ins = "INSERT INTO leave_balances (user_id, leave_type, used_days, remaining_days, total_days)
            VALUES (?, ?, 0, ?, ?)";
$stmt_ins = $conn->prepare($sql_ins);

foreach ($leave_types as $id => $info) {
    if (!isset($balances[$id])) {
        $def_days = $info['default_days'];
        // user_id (int), leave_type (int), remaining_days (int), total_days (int)
        $stmt_ins->bind_param("iiii", $user_id, $id, $def_days, $def_days);
        $stmt_ins->execute();

        $balances[$id] = [
            'leave_type'     => $id,
            'used_days'      => 0,
            'remaining_days' => $def_days,
            'total_days'     => $def_days
        ];
    }
}
$stmt_ins->close();

// 7. Optionally, calculate total used across all leaves
$totalUsedAll = 0;
foreach ($balances as $bal) {
    $totalUsedAll += (int)$bal['used_days'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css.css">
    <style>
        .form-container {
            width: 90%;
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #3498db;
            color: #fff;
        }
        .summary {
            font-style: italic;
            margin-top: 10px;
        }
        .actions a {
            margin-right: 8px;
        }
        .no-balance {
            color: #999;
        }
        .zero {
            color: red;
            font-weight: bold;
        }
    </style>
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
            <p class="summary">
                You have used a total of <strong><?php echo $totalUsedAll; ?></strong> leave days across all types.
            </p>
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
                        <?php
                            if (!isset($leave_types[$leave_id])) {
                                // If for some reason this ID isn't in $leave_types, skip
                                continue;
                            }
                            $leaveName = $leave_types[$leave_id]['name'];
                            $used      = (int)$bal['used_days'];
                            $remain    = (int)$bal['remaining_days'];
                            $total     = (int)$bal['total_days'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($leaveName); ?></td>
                            <td><?php echo $used > 0 ? $used : "<span class='no-balance'>0</span>"; ?></td>
                            <td><?php echo $remain > 0 ? $remain : "<span class='zero'>0</span>"; ?></td>
                            <td><?php echo $total; ?></td>
                            <td class="actions">
                                <!-- Example: link to apply for this specific leave type -->
                                <a href="apply_leave.php?type=<?php echo $leave_id; ?>">Apply</a>
                                <!-- Example: link to filter leave history by this leave type -->
                                <a href="leavehistory.php?filter=<?php echo urlencode($leaveName); ?>">History</a>
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

