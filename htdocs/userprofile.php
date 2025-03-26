<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details securely
$sql = "SELECT name, email, password FROM users WHERE userid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);

        $sql = "UPDATE users SET name=?, email=? WHERE userid=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $email, $user_id);
        $stmt->execute();
        $_SESSION['success'] = "Profile updated successfully.";
        $stmt->close();
    }

    // Handle Password Change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['error'] = "New passwords do not match.";
        } else {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE userid=?");
            $stmt->bind_param("si", $new_hashed_password, $user_id);
            $stmt->execute();
            $_SESSION['success'] = "Password changed successfully.";
            $stmt->close();
        }
    }
    header("Location: userprofile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="bootstrap1.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="d-flex">
    <!-- Sidebar -->
    <nav class="bg-dark text-white p-3 vh-100" style="width: 250px;">
    <h2 class="h5"> Profile</h2>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link text-white" href="user.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="apply_leave.php">Apply for Leave</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="leavehistory.php">Leave History</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="notifications.php">Notifications</a></li>
            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container p-4">
        <h2>Profile Settings</h2>

        <!-- Success & Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?> </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?> </div>
        <?php endif; ?>

        <div class="row">
            
            <!-- Profile Update -->
            <div class="col-md-6">
                <div class="card p-3 mb-4">
                    <h4>Update Profile</h4>
                    <form method="POST">
                        <label class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                        <label class="form-label mt-2">Email:</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                        <button type="submit" name="update_profile" class="btn btn-primary mt-3">Update</button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h4>Change Password</h4>
                    <form method="POST">
                        <label class="form-label">Current Password:</label>
                        <input type="password" name="current_password" class="form-control" required>

                        <label class="form-label mt-2">New Password:</label>
                        <input type="password" name="new_password" class="form-control" required>

                        <label class="form-label mt-2">Confirm New Password:</label>
                        <input type="password" name="confirm_password" class="form-control" required>

                        <button type="submit" name="change_password" class="btn btn-warning mt-3">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="bootstrap.bundle.min.js"></script>
</body>
</html>
