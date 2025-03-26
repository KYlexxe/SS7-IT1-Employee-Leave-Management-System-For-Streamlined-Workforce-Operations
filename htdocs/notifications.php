<?php
session_start();
include 'dbconnect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for this user, most recent first
$query = "
    SELECT n.*, lr.leave_type, lt.LeaveName, lr.Status 
    FROM notifications n
    JOIN leaverequest lr ON n.message LIKE CONCAT('%', lr.RequestID, '%') 
    JOIN leavetypes lt ON lr.leave_type = lt.LeaveTypeID
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Mark all notifications as read
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_read'])) {
    $updateQuery = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: notifications.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="bootstrap1.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="bg-dark text-white p-3 vh-100" style="width: 250px;">
            <h2 class="h5">Notifications</h2>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="user.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="apply_leave.php">Apply for Leave</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="leavehistory.php">Leave History</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="userprofile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container p-4">
            <h2>Notifications</h2>
            
            <?php if ($result->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li class="list-group-item <?php echo $row['is_read'] == 0 ? 'bg-light' : ''; ?>">
                            <p class="mb-1"> 
                                Your leave request for <strong><?php echo htmlspecialchars($row['LeaveName']); ?></strong> has been <strong><?php echo htmlspecialchars($row['Status']); ?></strong>.
                            </p>
                            <small class="text-muted"> <?php echo date("F j, Y, g:i A", strtotime($row['created_at'])); ?> </small>
                        </li>
                    <?php endwhile; ?>
                </ul>
                
                <form method="POST" class="mt-3">
                    <button type="submit" name="mark_read" class="btn btn-primary">Mark All as Read</button>
                </form>
            <?php else: ?>
                <p class="alert alert-info">No new notifications.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="bootstrap.bundle.min.js"></script>
</body>
</html>