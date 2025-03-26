<?php
include 'dbconnect.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Access Denied. You must be an admin to view this page.";
    exit();
}

// Handle leave request approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $employee_id = $_POST['employee_id'];
    $action = $_POST['action'];
    $leave_type = $_POST['leave_type'] ?? '';

    // Capture rejection reason if applicable
    $rejection_reason = "";
    if ($action == 'reject' && isset($_POST['rejection_reason'])) {
        $rejection_reason = trim($_POST['rejection_reason']);
    }

    $status = ($action == 'approve') ? 'Approved' : 'Rejected';

    // Update leave request status
    $update_query = "UPDATE leaverequest SET Status = ? WHERE RequestID = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('si', $status, $request_id);

    if ($stmt->execute()) {
        $stmt->close();

        // Fetch employee details
        $emp_query = "SELECT Email, Name FROM employee WHERE EmployeeID = ?";
        $emp_stmt = $conn->prepare($emp_query);
        $emp_stmt->bind_param("i", $employee_id);
        $emp_stmt->execute();
        $emp_result = $emp_stmt->get_result();
        $employee_data = $emp_result->fetch_assoc();
        $emp_stmt->close();

        // Notification message
        $notification_message = ($status == 'Rejected' && !empty($rejection_reason)) ?
            "Your leave request ($leave_type) has been rejected. Reason: $rejection_reason" :
            "Your leave request ($leave_type) has been $status.";

        // Insert notification
        $notif_query = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notif_stmt = $conn->prepare($notif_query);
        $notif_stmt->bind_param("is", $employee_id, $notification_message);
        $notif_stmt->execute();
        $notif_stmt->close();

        // Insert audit log
        $audit_message = "Leave request ID $request_id status updated to $status.";
        if ($status == 'Rejected' && !empty($rejection_reason)) {
            $audit_message .= " Rejection reason: $rejection_reason";
        }
        $audit_query = "INSERT INTO audit_log (request_id, changed_by, change_message) VALUES (?, ?, ?)";
        $changed_by = $_SESSION['user_id'];
        $audit_stmt = $conn->prepare($audit_query);
        $audit_stmt->bind_param("iis", $request_id, $changed_by, $audit_message);
        $audit_stmt->execute();
        $audit_stmt->close();

        // Send email notification
        $to = $employee_data['Email'];
        $subject = "Leave Request Status Update";
        $email_message = "Dear " . $employee_data['Name'] . ",\n\n" . $notification_message . "\n\nRegards,\nHR Team";
        $headers = "From: hr@yourcompany.com\r\nReply-To: hr@yourcompany.com\r\nX-Mailer: PHP/" . phpversion();
        mail($to, $subject, $email_message, $headers);

        echo "<p>Leave request $status successfully!</p>";
    } else {
        echo "<p>Error updating leave request: " . $stmt->error . "</p>";
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Leave Requests</title>
    <link rel="stylesheet" href="csss.css">
</head>
<body>
<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="employee.php">Employees</a></li>
        <li><a href="leavetypes.php">Leave Types</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="auditlog.php">Audit Log</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>


    <div class="container">
        
        <h2>Pending Leave Requests</h2>
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
                // Fetch pending leave requests
                $pending_query = "SELECT lr.RequestID, e.Name, lr.leave_type, lr.StartDate, lr.EndDate, lr.Status, lr.EmployeeID
                                  FROM leaverequest lr 
                                  JOIN employee e ON lr.EmployeeID = e.EmployeeID 
                                  WHERE lr.Status = 'Pending'";
                $pending_result = mysqli_query($conn, $pending_query);

                if (mysqli_num_rows($pending_result) > 0) {
                    while ($row = mysqli_fetch_assoc($pending_result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['leave_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['StartDate']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['EndDate']) . "</td>";
                        echo "<td>" . ucfirst(htmlspecialchars($row['Status'])) . "</td>";
                        echo "<td>
                                <form method='POST'>
                                    <input type='hidden' name='request_id' value='" . htmlspecialchars($row['RequestID']) . "'>
                                    <input type='hidden' name='employee_id' value='" . htmlspecialchars($row['EmployeeID']) . "'>
                                    <input type='hidden' name='leave_type' value='" . htmlspecialchars($row['leave_type']) . "'>

                                    <div id='rejection_" . htmlspecialchars($row['RequestID']) . "' style='display:none;'>
                                        <label for='rejection_reason'>Reason for rejection:</label>
                                        <textarea name='rejection_reason'></textarea>
                                    </div>
                                    
                                    <button type='submit' name='action' value='approve' class='approve'>Approve</button> <br><br>
                                    <button type='button' onclick='showRejection(" . htmlspecialchars($row['RequestID']) . ")' class='reject'>Reject</button> <br><br>
                                    <button type='submit' name='action' value='reject' id='submit_reject_" . htmlspecialchars($row['RequestID']) . "' style='display:none;'>Submit Rejection</button>
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

    <script>
    function showRejection(requestId) {
        document.getElementById('rejection_' + requestId).style.display = 'block';
        document.getElementById('submit_reject_' + requestId).style.display = 'inline-block';
    }
    </script>
</body>
</html>
