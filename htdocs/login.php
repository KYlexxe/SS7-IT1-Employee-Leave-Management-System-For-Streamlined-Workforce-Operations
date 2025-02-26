<?php 
// This part is optional if you want to start the session and include any preliminary code
session_start();
include 'dbconnect.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <header>Login Page</header>
        <form action="login.php" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="role">Login as:</label>
            <select name="role" id="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit">Login</button>
        </form>
        <div class="link">
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    </div>
</body>
</html>

<?php
// Process the login after the HTML if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; 

    $sql = "SELECT * FROM users WHERE email = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] == 'admin') {
                header("Location: admin.php"); 
            } else {
                header("Location: user.php"); 
            }
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "Invalid email or role!";
    }

    $stmt->close();
    $conn->close();
}   

if (isset($_GET['message'])) {
    if ($_GET['message'] == "already_registered") {
        echo "<p style='color: red;'>You are already registered! Please log in.</p>";
    } elseif ($_GET['message'] == "registration_successful") {
        echo "<p style='color: green;'>Registration successful! Please log in.</p>";
    }
}
?>
