<?php
include 'dbconnect.php';
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";

// Fetch user's leave balances
$balance_sql = "SELECT lb.leave_type, lb.remaining_days, lt.LeaveName 
                FROM leave_balances lb
                JOIN leavetypes lt ON lb.leave_type = lt.LeaveTypeID
                WHERE lb.user_id = ?";
$stmt_balance = $conn->prepare($balance_sql);
$stmt_balance->bind_param("i", $user_id);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();

$leaveBalances = [];
while ($row = $result_balance->fetch_assoc()) {
    $leaveBalances[$row['leave_type']] = [
        'name' => $row['LeaveName'],
        'days' => $row['remaining_days']
    ];
}
$stmt_balance->close();

// Process leave application
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $days_requested = $_POST['days_requested'] ?? 0;
    $status = 'Pending';

    // Validate inputs
    if (empty($leave_type) || empty($start_date) || empty($end_date) || $days_requested <= 0) {
        $error_message = "Please fill in all fields correctly.";
    } elseif (!isset($leaveBalances[$leave_type])) {
        $error_message = "Invalid leave type selected.";
    } elseif ($days_requested > $leaveBalances[$leave_type]['days']) {
        $error_message = "Insufficient leave balance. You only have " . $leaveBalances[$leave_type]['days'] . " days left.";
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error_message = "End date cannot be before the start date.";
    } else {
        // Insert leave request
        $sql = "INSERT INTO leaverequest (EmployeeID, leave_type, StartDate, EndDate, days_requested, Status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssis", $user_id, $leave_type, $start_date, $end_date, $days_requested, $status);
        if ($stmt->execute()) {
            $error_message = "Leave request submitted successfully.";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Leave</title>
    <link href="bootstrap1.css" rel="stylesheet">
    
    <script>
        // Automatically calculate days requested based on start and end dates
        function calculateDays() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            if (startDate && endDate && startDate <= endDate) {
                const timeDiff = endDate - startDate;
                const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1; // Include both start and end dates
                document.getElementById('days_requested').value = daysDiff;
            } else {
                document.getElementById('days_requested').value = 0;
            }
        }
    </script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar (unchanged) -->
        <nav class="bg-dark text-white p-3 vh-100" style="width: 250px;">
            <h2 class="h5">Apply Leave</h2>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="user.php" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item"><a href="leavehistory.php" class="nav-link text-white">Leave History</a></li>
                <li class="nav-item"><a href="userprofile.php" class="nav-link text-white">Profile</a></li>
                <li class="nav-item"><a href="notifications.php" class="nav-link text-white">Notifications</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-danger">Logout</a></li>
            </ul>
        </nav>
        
        <!-- Content -->
        <div class="container p-4">
            <h2>Apply for Leave</h2>
            <?php if ($error_message): ?>
                <div class="alert <?php echo strpos($error_message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?>">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="leave_type" class="form-label">Leave Type:</label>
                    <select id="leave_type" name="leave_type" class="form-select" required>
                        <option value="">-- Select Leave Type --</option>
                        <?php foreach ($leaveBalances as $type => $data) { ?>
                            <option value="<?php echo htmlspecialchars($type); ?>">
                                <?php echo htmlspecialchars($data['name']); ?> (<?php echo $data['days']; ?> days left)
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required onchange="calculateDays()">
                </div>
                <div class="mb-3">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required onchange="calculateDays()">
                </div>
                <div class="mb-3">
                    <label for="days_requested" class="form-label">Days Requested:</label>
                    <input type="number" id="days_requested" name="days_requested" class="form-control" min="1" required readonly>
                    <small class="text-muted">Days are calculated automatically based on the start and end dates.</small>
                </div>
                <button type="submit" class="btn btn-primary">Apply</button>
            </form>
        </div>
    </div>
    <script src="bootstrap.bundle.min.js"></script>
</body>
</html>