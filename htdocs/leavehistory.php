<?php 
include 'dbconnect.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID from session

// Fetch leave history
$sql = "SELECT leave_type, StartDate, EndDate, Status FROM leaverequest WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Bind the session user ID to the query
$stmt->execute();
$result = $stmt->get_result();
?>

<html>
<head>
    <title>Leave History</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>

    <header>
        <h1>Leave History</h1>
    </header>
    <nav>
        <ul>
            <li><a href="user.php">Home</a></li>
            <li><a href="apply_leave.php">Apply Leave</a></li>  
            <li><a href="userprofile.php">Profile</a></li>  
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <br>
    <div class="form-container">
        <h2>Leave History</h2>
        <table border="1" cellpadding="10">
            <tr>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                <td><?php echo htmlspecialchars($row['StartDate']); ?></td>
                <td><?php echo htmlspecialchars($row['EndDate']); ?></td>
                <td><?php echo htmlspecialchars($row['Status']); ?></td>
            </tr>
            <?php } ?>
        </table>
            </div>
</body>
</html>
