<?php
session_start();
include 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT name, email, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];

        $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $email, $user_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: userprofile.php");
        exit();
    }

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (password_verify($current_password, $result['password'])) {
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_password, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success'] = "Password changed successfully!";
        } else {
            $_SESSION['error'] = "Incorrect current password!";
        }
        header("Location: userprofile.php");
        exit();
    }

    if (isset($_POST['upload_picture']) && isset($_FILES['profile_picture'])) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);

            $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success'] = "Profile picture updated!";
        } else {
            $_SESSION['error'] = "Only JPG, JPEG, and PNG files are allowed.";
        }
        header("Location: userprofile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header>
        <h1>Profile</h1>
    </header>

    <nav>
        <ul>
            <li><a href="user.php">Home</a></li>
            <li><a href="apply_leave.php">Apply for Leave</a></li>
            <li><a href="leavehistory.php">Leave History</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
    <div class="form-container">
        <section>
            <h2>Update Profile</h2>
           
            <form method="POST">
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                
                <button type="submit" name="update_profile">Update</button>
            </form>
        </section>
    </div>
    <div class="form-container">
        <section>
            <h2>Change Password</h2>
            
            <form method="POST">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>

                <label>New Password:</label>
                <input type="password" name="new_password" required>

                <button type="submit" name="change_password">Change Password</button>
            </form>
        </section>
        </div>
        <div class="form-container">
        <section>
            <h2>Profile Picture</h2>
            <?php if (!empty($user['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" width="100" height="100" alt="Profile Picture">
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" accept="image/*" required>
                <button type="submit" name="upload_picture">Upload</button>
            </form>
        </section>
            </div>
    </main>
</body>
</html>
