<?php
session_start();
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture form data
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $password   = trim($_POST['password']);
    $position   = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $role       = 'user';  // self-registration always creates a "user" account

    // Basic validation: all fields required
    if (empty($name) || empty($email) || empty($password) || empty($position) || empty($department)) {
        $error = "Please fill in all required fields.";
    } else {
        // Check if email is already registered
        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into the users table
            $insertQuery = "INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                
                // Insert corresponding employee record
                $insertEmpQuery = "INSERT INTO employee (EmployeeID, name, email, position, department, Hire_Date) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmtEmp = $conn->prepare($insertEmpQuery);
                $stmtEmp->bind_param("issss", $user_id, $name, $email, $position, $department);
                $stmtEmp->execute();
                $stmtEmp->close();
                
                // Retrieve default leave balances based on position from leave_policy table
                $policy_sql = "SELECT allowed_days, leave_type FROM leave_policy WHERE position = ?";
                $stmtPolicy = $conn->prepare($policy_sql);
                $stmtPolicy->bind_param("s", $position);
                $stmtPolicy->execute();
                $resultPolicy = $stmtPolicy->get_result();
                
                $defaults = [];
                if ($resultPolicy->num_rows > 0) {
                    while ($policy = $resultPolicy->fetch_assoc()) {
                        // Store allowed_days for each leave_type defined in the policy
                        $defaults[$policy['leave_type']] = $policy['allowed_days'];
                    }
                } else {
                    // Fallback defaults if no policy found
                    $defaults['vacation_leave'] = 12;
                    $defaults['sick_leave'] = 7;
                }
                $stmtPolicy->close();
                
                // Insert default leave balances into leave_balances table.
                // Here we assume leave_balances stores one row per leave type per user.
                $insertLB = "INSERT INTO leave_balances (user_id, leave_type, used_days, remaining_days, total_days) VALUES (?, ?, 0, ?, ?)";
                $stmtLB = $conn->prepare($insertLB);
                foreach ($defaults as $leave_type_default => $allowed_days) {
                    $stmtLB->bind_param("isii", $user_id, $leave_type_default, $allowed_days, $allowed_days);
                    $stmtLB->execute();
                }
                $stmtLB->close();
                
                // Registration successful, redirect to login with a success message.
                header("Location: login.php?message=registration_successful");
                exit();
            } else {
                $error = "Registration failed: " . $stmt->error;
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
  <title>User Registration</title>
  <link rel="stylesheet" href="css.css">
  <style>
    body {
        background-color: #f4f7fc;
        font-family: 'Roboto', sans-serif;
        padding: 20px;
    }
    .form-container {
        width: 90%;
        max-width: 400px;
        margin: 50px auto;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .form-container h1 {
        text-align: center;
        margin-bottom: 20px;
    }
    .form-container label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-container input[type="text"],
    .form-container input[type="email"],
    .form-container input[type="password"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }
    .form-container select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
        background: #fff;
    }
    .form-container button {
        width: 100%;
        padding: 12px;
        background-color: #3498db;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
    }
    .form-container button:hover {
        background-color: #2980b9;
    }
    .form-container p {
        text-align: center;
        margin-top: 10px;
    }
    .form-container a {
        color: #3498db;
        text-decoration: none;
    }
  </style>
  <script>
    // JavaScript object mapping departments to positions
    const departmentPositions = {
      "IT": ["IT Manager", "Systems Administrator", "Network Engineer"],
      "Administration": ["Office Manager", "Administrative Assistant"],
      "Finance": ["Accountant", "Financial Analyst", "Controller"],
      "HR": ["HR Manager", "HR Assistant", "Recruitment Specialist"],
      "Sales & Marketing": ["Sales Representative", "Marketing Manager", "Customer Service Representative"],
      "Production": ["Production Supervisor", "Production Operator", "Quality Control Inspector"]
    };

    // When the department dropdown changes, update the position dropdown options.
    function updatePositions() {
      const deptSelect = document.getElementById("department");
      const posSelect = document.getElementById("position");
      const selectedDept = deptSelect.value;
      
      // Clear current options in the position dropdown
      posSelect.innerHTML = '<option value="">-- Select Position --</option>';
      
      // Populate the position dropdown based on the selected department
      if (departmentPositions[selectedDept]) {
        departmentPositions[selectedDept].forEach(function(position) {
          const option = document.createElement("option");
          option.value = position;
          option.text = position;
          posSelect.appendChild(option);
        });
      }
    }
    
    // Set up event listener once the document is ready
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById("department").addEventListener("change", updatePositions);
    });
  </script>
</head>
<body>
  <div class="form-container">
    <h1>Register</h1>
    <?php if(isset($error)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="register.php" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        
        <!-- Department Dropdown (required) -->
        <label for="department">Department:</label>
        <select id="department" name="department" required>
            <option value="">-- Select Department --</option>
            <option value="IT">IT</option>
            <option value="Administration">Administration</option>
            <option value="Finance">Finance</option>
            <option value="HR">HR</option>
            <option value="Sales & Marketing">Sales & Marketing</option>
            <option value="Production">Production</option>
        </select>
        
        <!-- Position Dropdown (required) -->
        <label for="position">Position:</label>
        <select id="position" name="position" required>
            <option value="">-- Select Position --</option>
            <!-- Options will be dynamically populated based on the selected department -->
        </select>
        
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
  </div>
</body>
</html>
