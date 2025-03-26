<?php
include 'dbconnect.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all approved leave requests
$sql = "SELECT lr.StartDate, lr.EndDate, e.Name AS EmployeeName, lr.leave_type
        FROM leaverequest lr
        JOIN employee e ON lr.EmployeeID = e.EmployeeID
        WHERE lr.Status = 'Approved'";
$result = mysqli_query($conn, $sql);

$leave_summary = [];
while ($row = mysqli_fetch_assoc($result)) {
    $leave_summary[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Summary</title>
    <link rel="stylesheet" href="css.css">
    <style>
        body { font-family: 'Poppins', sans-serif; text-align: center; background: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
        .back-btn { text-decoration: none; color: #fff; font-weight: bold; margin-top: 20px; padding: 10px 15px; background: #007bff; border-radius: 6px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Leave Summary</h1>
        <table>
            <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
            <?php foreach ($leave_summary as $leave) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($leave['EmployeeName']); ?></td>
                    <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                    <td><?php echo htmlspecialchars($leave['StartDate']); ?></td>
                    <td><?php echo htmlspecialchars($leave['EndDate']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a href="calendar.php" class="back-btn">Back to Calendar</a>
    </div>
</body>
</html>
