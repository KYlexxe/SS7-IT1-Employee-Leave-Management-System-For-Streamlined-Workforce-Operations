<?php
session_start();
include 'dbconnect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email exists
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $token = bin2hex(random_bytes(10)); // Shorter token
            $expires = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token valid for 1 hour

            // Store token in database
            $sql = "UPDATE users SET reset_token=?, reset_expires=? WHERE email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $token, $expires, $email);

            if ($stmt->execute()) {
                $_SESSION['reset_token'] = $token;
                $_SESSION['reset_email'] = $email; // Store email for reset

                // Success message with a button
                $success = "Your reset token is: <strong>$token</strong>. Copy it and click the button below to reset your password.<br>
                            <a href='resetpassword.php' class='btn btn-success mt-3'>Go to Reset Password</a>";
            } else {
                $error = "Error updating reset token. Please try again.";
            }
        } else {
            $error = "No account found with that email.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password</title>
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
            <h3 class="text-center">Forgot Password</h3>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Enter Your Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Generate Reset Token</button>
            </form>
        </div>
    </div>
</body>
</html>
