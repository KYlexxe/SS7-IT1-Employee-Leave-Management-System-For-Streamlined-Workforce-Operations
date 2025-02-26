<?php
session_start();
include 'dbconnect.php';

// Check if the user is logged in and has the role 'user'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for this user, most recent first
$query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Notifications</title>
    <link rel="stylesheet" href="css.css">
    <style>
        /* Example inline CSS for notifications */
        .notifications {
            list-style: none;
            margin: 20px auto;
            max-width: 600px;
            padding: 0;
        }
        .notifications li {
            background: #f4f7fc;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .notifications li small {
            display: block;
            color: #777;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Your Notifications</h1>
    </header>
    <nav>
        <ul>
            <li><a href="user.php">Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <ul class="notifications">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <p><?php echo htmlspecialchars($row['message']); ?></p>
                        <small><?php echo htmlspecialchars($row['created_at']); ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No notifications available.</p>
        <?php endif; ?>
    </div>
</body>
</html>
