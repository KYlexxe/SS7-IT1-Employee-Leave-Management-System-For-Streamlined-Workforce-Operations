<?php  
include 'dbconnect.php'; // Ensure the database connection is correct
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = ""; // Variable to hold any error/warning messages

// Ensure a corresponding employee record exists in the employee table
$sql = "SELECT * FROM employee WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // No employee record exists, so create one using data from the users table
    $userQuery = "SELECT name, email FROM users WHERE id = ?";
    $stmtUser = $conn->prepare($userQuery);
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $userResult = $stmtUser->get_result();
    $userData = $userResult->fetch_assoc();
    $stmtUser->close();

    // Insert a new employee record.
    // Using exact column names: EmployeeID, name, email, position, department, Hire_Date.
    $insertEmp = "INSERT INTO employee (EmployeeID, name, email, position, department, Hire_Date) 
                  VALUES (?, ?, ?, '', '', NOW())";
    $stmtInsert = $conn->prepare($insertEmp);
    $stmtInsert->bind_param("iss", $user_id, $userData['name'], $userData['email']);
    if (!$stmtInsert->execute()) {
        echo "Error creating employee record: " . $stmtInsert->error;
        exit();
    }
    $stmtInsert->close();
}
$stmt->close();

// Retrieve the employee record to get the position and department
$sql_emp = "SELECT * FROM employee WHERE EmployeeID = ?";
$stmt_emp = $conn->prepare($sql_emp);
$stmt_emp->bind_param("i", $user_id);
$stmt_emp->execute();
$result_emp = $stmt_emp->get_result();
$employee = $result_emp->fetch_assoc();
$stmt_emp->close();

// Process the apply leave form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data using null coalescing operators
    $leave_type     = $_POST['leave_type'] ?? '';
    $start_date     = $_POST['start_date'] ?? '';
    $end_date       = $_POST['end_date'] ?? '';
    $days_requested = $_POST['days_requested'] ?? '';
    $status         = 'Pending'; // Default status for new leave requests

    // Simple validation
    if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($days_requested)) {
        $error_message = "Please fill in all the fields.";
    } else {
        // -----------------------------
        // Overlapping Leave Validation
        // -----------------------------
        // Overlap condition: existing StartDate <= new EndDate AND existing EndDate >= new StartDate.
        $overlap_sql = "SELECT COUNT(*) AS overlap_count 
                        FROM leaverequest 
                        WHERE Status IN ('Approved', 'Pending') 
                          AND (StartDate <= ? AND EndDate >= ?)";
        $stmt_overlap = $conn->prepare($overlap_sql);
        $stmt_overlap->bind_param("ss", $end_date, $start_date);
        $stmt_overlap->execute();
        $result_overlap = $stmt_overlap->get_result();
        $overlap_data = $result_overlap->fetch_assoc();
        $overlap_count = (int)$overlap_data['overlap_count'];
        $stmt_overlap->close();
        
        $overlap_threshold = 3;
        if ($overlap_count >= $overlap_threshold) {
            // Notify HR via email
            $hr_email = 'admin@gmail.com'; 
            $subject = "Overlapping Leave Request Automatically Rejected";
            $headers = "From: noreply@yourcompany.com\r\n" .
                       "Reply-To: noreply@yourcompany.com\r\n" .
                       "X-Mailer: PHP/" . phpversion();
            $warning_message = "Warning: Leave request from user ID $user_id, scheduled from $start_date to $end_date, overlaps with $overlap_count other leave requests. This submission was automatically rejected.";
            mail($hr_email, $subject, $warning_message, $headers);
            
            // Insert a notification for the employee
            $notification_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $stmt_notification = $conn->prepare($notification_sql);
            $notification_message = "Your leave request for $start_date to $end_date was automatically rejected due to overlapping with $overlap_count other leave requests. Please try again with a different date range.";
            $stmt_notification->bind_param("is", $user_id, $notification_message);
            $stmt_notification->execute();
            $stmt_notification->close();
            
            $error_message = "Your leave request was automatically rejected due to overlapping leave requests. Please try again with a different date range.";
        } else {
            // Insert the leave request
            $sql = "INSERT INTO leaverequest (EmployeeID, leave_type, StartDate, EndDate, days_requested, Status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $error_message = "Prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param("isssis", $user_id, $leave_type, $start_date, $end_date, $days_requested, $status);
                if ($stmt->execute()) {
                    $error_message = "Leave request submitted successfully!";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Retrieve available leave options for this employee from the leave_policy table.
// This query fetches both specific policies (matching employee's position and department)
// and includes additional leave types by using an IN clause.
$policy_sql = "SELECT leave_type, allowed_days 
               FROM leave_policy 
               WHERE (position = ? AND department = ?)
                  OR leave_type IN ('Maternity Leave', 'Paternity Leave', 'Emergency Leave', 'Bereavement Leave', 'Special Privilege Leave', 'Study Leave', 'Leave Without Pay', 'Compensatory Leave')";
$stmt_policy = $conn->prepare($policy_sql);
$stmt_policy->bind_param("ss", $employee['position'], $employee['department']);
$stmt_policy->execute();
$result_policy = $stmt_policy->get_result();
$leaveOptions = [];
while ($row = $result_policy->fetch_assoc()) {
    $leaveOptions[] = $row;
}
$stmt_policy->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Apply for Leave</title>
    <link rel="stylesheet" href="css.css">
    <style>
        .form-container {
            width: 30%;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .error-message {
            text-align: center;
            color: red;
            margin-bottom: 10px;
        }
        .success-message {
            text-align: center;
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Dashboard</h1>
    </header>
    <nav>
        <ul>
            <li><a href="user.php">Home</a></li>
            <li><a href="leavehistory.php">Leave History</a></li>
            <li><a href="userprofile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="form-container">
        <h2>Apply for Leave</h2>
        <?php 
            if (!empty($error_message)) {
                if (strpos($error_message, "submitted successfully") !== false) {
                    echo "<p class='success-message'>" . htmlspecialchars($error_message) . "</p>";
                } else {
                    echo "<p class='error-message'>" . htmlspecialchars($error_message) . "</p>";
                }
            }
        ?>
        <form method="POST">
            <label for="leave_type">Leave Type:</label>
            <select id="leave_type" name="leave_type" required>
                <option value="">-- Select Leave Type --</option>
                <?php foreach ($leaveOptions as $option) { ?>
                    <option value="<?php echo htmlspecialchars($option['leave_type']); ?>">
                        <?php echo htmlspecialchars($option['leave_type']); ?> (<?php echo htmlspecialchars($option['allowed_days']); ?> days)
                    </option>
                <?php } ?>
            </select>
            <br><br>
            
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
            <br><br>
            
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>
            <br><br>
            
            <label for="days_requested">Number of Days Requested:</label>
            <input type="number" id="days_requested" name="days_requested" min="1" required>
            <br><br>
            
            <button type="submit">Apply</button>
        </form>
    </div>
</body>
</html>
