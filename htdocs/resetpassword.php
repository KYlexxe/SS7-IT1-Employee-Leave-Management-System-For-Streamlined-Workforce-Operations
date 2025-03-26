<?php
session_start();
include 'dbconnect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $token = $_POST['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Verify token
    $sql = "SELECT * FROM users WHERE email=? AND reset_token=? AND reset_expires > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update password and clear token
        $sql = "UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_password, $email);
        $stmt->execute();

        $success = "Your password has been updated. <a href='login.php'>Login here</a>";
    } else {
        $error = "Invalid or expired token!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container {
            max-width: 450px;
            margin: 80px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-primary { font-size: 18px; padding: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h3 class="text-center">Reset Password</h3>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
            <form action="" method="post">
                <input type="hidden" name="email" value="<?php echo isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : ''; ?>">
                <div class="mb-3">
                    <label for="token" class="form-label">Enter Reset Token:</label>
                    <input type="text" name="token" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>
