<?php
include 'dbconnect.php'; // Ensure the database connection is correct
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "Access Denied. You must be an admin to view this page.";
    exit();
}

// Handle form submission for approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the request data
    $request_id = $_POST['request_id'];
    $employee_id = $_POST['employee_id'];
    $action = $_POST['action']; // Either 'approve' or 'reject'

    // Determine the new status based on the action
    $status = $action == 'approve' ? 'Approved' : 'Rejected';

    // Update the status of the leave request
    $query = "UPDATE leaverequest SET Status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $status, $request_id);

    if ($stmt->execute()) {
        // Send notification to the employee (email or other)
        // Assuming we send an email or internal notification (Example: Simple Mail Function)
        $employee_query = "SELECT Email, Name FROM employee WHERE EmployeeID = ?";
        $employee_stmt = $conn->prepare($employee_query);
        $employee_stmt->bind_param('i', $employee_id);
        $employee_stmt->execute();
        $employee_result = $employee_stmt->get_result();
        $employee_data = $employee_result->fetch_assoc();

        $to = $employee_data['Email'];
        $subject = "Leave Request Status";
        $message = "Dear " . $employee_data['Name'] . ",\n\nYour leave request has been " . $status . ".\n\nThank you!";
        $headers = "From: admin@yourcompany.com";

        // Send the email (this is just an example, use a mail function or API)
        mail($to, $subject, $message, $headers);

        echo "<p>Leave request " . $status . " successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
}

// Fetch pending leave requests
$query = "SELECT lr.id, e.Name, lr.leave_type, lr.StartDate, lr.EndDate, lr.Status, lr.EmployeeID
          FROM leaverequest lr 
          JOIN employee e ON lr.EmployeeID = e.EmployeeID 
          WHERE lr.Status = 'Pending'"; // Only get pending requests
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
</header>

<nav>
    <ul>
        <li><a href="admin.php">Admin</a></li>
        <li><a href="employees.php">Employees</a></li>
        <li><a href="leave_requests.php">Leave Requests</a></li>
        <li><a href="leavetypes.php">Leave Types</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>Pending Leave Requests</h2>
    
    <!-- Table to display leave requests -->
    <table class="leave-requests">
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['Name'] . "</td>";
                    echo "<td>" . $row['leave_type'] . "</td>";
                    echo "<td>" . $row['StartDate'] . "</td>";
                    echo "<td>" . $row['EndDate'] . "</td>";
                    echo "<td>" . ucfirst($row['Status']) . "</td>";
                    echo "<td>
                            <form method='POST'>
                                <input type='hidden' name='request_id' value='" . $row['id'] . "'>
                                <input type='hidden' name='employee_id' value='" . $row['EmployeeID'] . "'>
                                <button type='submit' name='action' value='approve' class='approve'>Approve</button>
                                <button type='submit' name='action' value='reject' class='reject'>Reject</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No pending leave requests.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
