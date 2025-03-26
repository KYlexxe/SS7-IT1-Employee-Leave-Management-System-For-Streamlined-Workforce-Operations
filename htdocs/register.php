<?php
session_start();
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $password   = trim($_POST['password']);
    $position   = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $role       = 'user';
    $status     = 'Pending';
    
    if (empty($name) || empty($email) || empty($password) || empty($position) || empty($department)) {
        $error = "Please fill in all required fields.";
    } else {
        $checkQuery = "SELECT userid FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $conn->begin_transaction();
            try {
                $insertQuery = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($insertQuery);
                $stmt->bind_param("sssss", $name, $email, $hashedPassword, $role, $status);
                $stmt->execute();
                
                $conn->commit();
                header("Location: login.php?message=registration_pending");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Registration failed: " . $e->getMessage();
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="bootstrap1.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .register-container {
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <h3 class="text-center">Register</h3>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form action="register.php" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="department" class="form-label">Department</label>
                    <select id="department" name="department" class="form-select" required></select>
                </div>
                <div class="mb-3">
                    <label for="position" class="form-label">Position</label>
                    <select id="position" name="position" class="form-select" required></select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
    <script>
        const departmentPositions = {
            "IT": ["IT Manager", "Systems Administrator", "Network Engineer"],
            "Administration": ["Office Manager", "Administrative Assistant"],
            "Finance": ["Accountant", "Financial Analyst", "Controller"],
            "HR": ["HR Manager", "HR Assistant", "Recruitment Specialist"],
            "Sales & Marketing": ["Sales Representative", "Marketing Manager", "Customer Service Representative"],
            "Production": ["Production Supervisor", "Production Operator", "Quality Control Inspector"]
        };

        function updatePositions() {
            const deptSelect = document.getElementById("department");
            const posSelect = document.getElementById("position");
            const selectedDept = deptSelect.value;

            posSelect.innerHTML = '<option value="">-- Select Position --</option>';
            
            if (departmentPositions[selectedDept]) {
                departmentPositions[selectedDept].forEach(function(position) {
                    const option = document.createElement("option");
                    option.value = position;
                    option.text = position;
                    posSelect.appendChild(option);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const deptSelect = document.getElementById("department");
            deptSelect.addEventListener("change", updatePositions);

            Object.keys(departmentPositions).forEach(dept => {
                const option = document.createElement("option");
                option.value = dept;
                option.text = dept;
                deptSelect.appendChild(option);
            });

            updatePositions();
        });
    </script>
    <script src="bootstrap.bundle.min.js"></script>
</body>
</html>