<?php  
include 'dbconnect.php';
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// If an employee is being edited, capture the EmployeeID from the GET parameter.
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $employee_id = $_GET['edit'];
    
    // If the form is submitted for updating, process the update.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Capture updated values
        $name       = $_POST['name'] ?? '';
        $email      = $_POST['email'] ?? '';
        $position   = $_POST['position'] ?? '';
        $department = $_POST['department'] ?? '';
        $hire_date  = $_POST['hire_date'] ?? '';
        
        // Prepare update query
        $update_sql = "UPDATE employee SET name = ?, email = ?, position = ?, department = ?, Hire_Date = ? WHERE EmployeeID = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("sssssi", $name, $email, $position, $department, $hire_date, $employee_id);
        
        if ($stmt_update->execute()) {
            $message = "Employee details updated successfully.";
        } else {
            $message = "Error updating employee details: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
    
    // Fetch current employee details for editing.
    $sql = "SELECT * FROM employee WHERE EmployeeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Management</title>
  <link rel="stylesheet" href="csss.css">
</head>
<body>
 
  <div class="sidebar">
    <h2>Employee Management</h2>
    <ul>
        <li><a href="admin.php">Admin Panel</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="leavetypes.php">Leave Types</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="auditlog.php">Audit Log</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
  <div class="container"> 
  <?php if (isset($message)): ?>
      <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
          <?php echo htmlspecialchars($message ?? ''); ?>
      </div>
  <?php endif; ?>

  <?php if (isset($employee)): ?>
      <h2>Edit Employee Details</h2>
      <form method="POST">
          <label for="name">Name:</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($employee['name'] ?? ''); ?>" required>
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>" required>
          <label for="position">Position:</label>
          <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($employee['position'] ?? ''); ?>">
          <label for="department">Department:</label>
          <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($employee['department'] ?? ''); ?>">
          <label for="hire_date">Hire Date:</label>
          <input type="date" id="hire_date" name="hire_date" value="<?php echo htmlspecialchars($employee['Hire_Date'] ?? ''); ?>">
          <button type="submit">Update Employee</button>
      </form>
      <a href="employee.php" class="button">Back to Employee List</a>
  <?php else: ?>
      <?php
      // Display a list of employees
      $list_sql = "SELECT * FROM employee";
      $result_list = mysqli_query($conn, $list_sql);
      if (mysqli_num_rows($result_list) > 0) {
          echo "<table>";
          echo "<thead><tr>
                    <th>EmployeeID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Hire Date</th>
                    <th>Action</th>
                </tr></thead><tbody>";
          while ($emp = mysqli_fetch_assoc($result_list)) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($emp['EmployeeID'] ?? '') . "</td>";
              echo "<td>" . htmlspecialchars($emp['name'] ?? '') . "</td>";
              echo "<td>" . htmlspecialchars($emp['email'] ?? '') . "</td>";
              echo "<td>" . htmlspecialchars($emp['position'] ?? '') . "</td>";
              echo "<td>" . htmlspecialchars($emp['department'] ?? '') . "</td>";
              echo "<td>" . htmlspecialchars($emp['Hire_Date'] ?? '') . "</td>";
              echo "<td><a href='employee.php?edit=" . htmlspecialchars($emp['EmployeeID'] ?? '') . "' class='button'>Edit</a></td>";
              echo "</tr>";
          }
          echo "</tbody></table>";
      } else {
          echo "<p>No employees found.</p>";
      }
      ?>
  <?php endif; ?>
  </div>
</body>
</html>
