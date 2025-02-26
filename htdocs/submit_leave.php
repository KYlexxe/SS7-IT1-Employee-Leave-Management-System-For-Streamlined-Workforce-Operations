<?php   
session_start();
include 'dbconnect.php'; // Include your DB connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the form data
$leave_type = $_POST['leave_type'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$remarks = $_POST['remarks'];

// Check if the user has enough leave balance (for simplicity, let's assume 10 vacation and 5 sick days)
$leave_sql = "SELECT remaining_days FROM leave_balances WHERE user_id = ? AND leave_type = ?";
$stmt_leave = $conn->prepare($leave_sql);
$stmt_leave->bind_param("is", $user_id, $leave_type);
$stmt_leave->execute();
$result_leave = $stmt_leave->get_result();

if ($result_leave->num_rows > 0) {
    $leave = $result_leave->fetch_assoc();
    $remaining_days = $leave['remaining_days'];

    // Assuming we want to leave at least 1 day for the leave
    if ($remaining_days > 0) {
        // Insert the leave request into the database
        $insert_leave_sql = "INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, remarks) 
                             VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_leave_sql);
        $stmt_insert->bind_param("issss", $user_id, $leave_type, $start_date, $end_date, $remarks);
        $stmt_insert->execute();

        // Reduce the remaining leave balance
        $new_remaining_days = $remaining_days - 1;  // Assuming 1 day per leave request
        $update_leave_sql = "UPDATE leave_balances SET remaining_days = ? WHERE user_id = ? AND leave_type = ?";
        $stmt_update = $conn->prepare($update_leave_sql);
        $stmt_update->bind_param("iis", $new_remaining_days, $user_id, $leave_type);
        $stmt_update->execute();

        echo "Leave request submitted successfully!";
    } else {
        echo "Insufficient leave balance!";
    }
} else {
    echo "No leave balance found for this leave type!";
}

$stmt_leave->close();
$stmt_insert->close();
$stmt_update->close();
$conn->close();

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
