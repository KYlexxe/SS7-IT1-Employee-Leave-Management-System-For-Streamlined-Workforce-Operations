<?php
session_start();
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    $sql = "SELECT * FROM users WHERE email = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ✅ If role is admin, allow login immediately
        if ($user['role'] === 'admin' || $user['status'] === 'Approved') {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['userid'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: admin.php");
                    exit();
                } else {
                    header("Location: user.php");
                    exit();
                }
            } else {
                $error = "Invalid password!";
            }
        } else {
            //  Only block users who are NOT approved
            $error = "Your account is pending approval. Please wait for admin verification.";
        }
    } else {
        $error = "Invalid email or role!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="bootstrap1.css" rel="stylesheet">
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
        .login-container h3 {
            font-size: 26px;
        }
        .form-label {
            font-size: 16px;
        }
        .form-control {
            font-size: 16px;
            padding: 12px;
        }
        .btn-primary {
            font-size: 18px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h3 class="text-center">Login</h3>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Login as:</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <p class="mt-2 text-center"><a href="forgotpassword.php">Forgot Password?</a></p>

            </form>
            <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    </div>
</body>
</html>
