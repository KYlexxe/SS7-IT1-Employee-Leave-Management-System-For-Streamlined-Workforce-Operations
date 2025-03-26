<?php
include 'dbconnect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $user_id = $_SESSION['user_id'];

    // Ensure only the logged-in user can cancel their own leave request
    $sql = "DELETE FROM leaverequest WHERE RequestID = ? AND EmployeeID = ? AND Status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $request_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['message'] = "Leave request canceled successfully.";
    } else {
        $_SESSION['error'] = "Error canceling leave or leave already processed.";
    }

    $stmt->close();
}
header("Location: leavehistory.php");
exit();
?>
