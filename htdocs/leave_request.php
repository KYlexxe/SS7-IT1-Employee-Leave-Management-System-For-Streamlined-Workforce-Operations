<?php
include 'dbconnect.php'; 
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit();
}

$employee_id = $_SESSION['user_id']; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = 'Pending'; 

    
    if (empty($leave_type) || empty($start_date) || empty($end_date)) {
        echo "Please fill in all the fields.";
    } else {
        
        $sql = "INSERT INTO leaverequest (EmployeeID, leave_type, StartDate, EndDate, Status) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $employee_id, $leave_type, $start_date, $end_date, $status);

        
        if ($stmt->execute()) {
            echo "Leave request submitted successfully!";
           
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<html>
<head>
    <title>Apply for Leave</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>

<header>
        <h1>Dashboard</h1>
    </header>
    <nav>
        <ul>
            <li><a href="admin.php">Admin</a></li> 
            <li><a href="employee.php">Employees</a></li>
            <li><a href="leave_request.php">Leave Requests</a></li> 
            <li><a href="leavetypes.php">Leave Types</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <h2>Apply for Leave</h2>
    <form method="POST">
        <label for="leave_type">Leave Type:</label>
        <input type="text" id="leave_type" name="leave_type" required><br><br>
        
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required><br><br>
        
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required><br><br>
        
        <button type="submit">Apply</button>
    </form>
</body>
</html>
