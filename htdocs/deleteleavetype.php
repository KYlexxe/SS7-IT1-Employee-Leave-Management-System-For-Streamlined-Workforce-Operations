<?php
include 'dbconnect.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $leaveID = $_GET['id'];

    $sql = "DELETE FROM leavetypes WHERE LeaveTypeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $leaveID);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Leave type deleted!";
    } else {
        $_SESSION['message'] = "Error deleting leave type.";
    }
}

header("Location: leavetypes.php");
exit();
?>
