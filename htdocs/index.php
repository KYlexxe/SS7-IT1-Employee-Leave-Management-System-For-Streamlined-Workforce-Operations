<?php
// index.php - Landing page with login and register options
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="bootstrap1.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            font-family: 'Arial', sans-serif;
        }
        .welcome-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .welcome-container h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1.5rem;
        }
        .btn-custom {
            padding: 0.75rem 1.5rem;
            font-size: 1.1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-custom-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-custom-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-custom-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-custom-success:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .btn-custom i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="welcome-container">
        <h1>Welcome to Employee Leave Management System</h1>
        <div class="d-flex justify-content-center gap-4">
            <a href="login.php" class="btn btn-custom btn-custom-primary">
                <i class="fa fa-sign-in-alt"></i> Login
            </a>
            <a href="register.php" class="btn btn-custom btn-custom-success">
                <i class="fa fa-user-plus"></i> Register
            </a>
        </div>
    </div>
</body>
</html>