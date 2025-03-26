<?php
session_start();
include 'dbconnect.php';

// Ensure only admins can perform this action
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    if (isset($_POST['approve'])) {
        // Approve the user by updating status
        $query = "UPDATE users SET status = 'Approved' WHERE id = ?";
    } elseif (isset($_POST['reject'])) {
        // Reject the user by deleting the record
        $query = "DELETE FROM users WHERE id = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the pending users page
    header("Location: pendingusers.php");
    exit();
} else {
    die("Invalid request.");
}
?>
