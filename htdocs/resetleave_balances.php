<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'dbconnect.php';

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) != 'admin') {
    die("Access denied. Only admins can perform this action.");
}

// Get the current year
$current_year = date('Y');

// Fetch all employees
$sql_employees = "SELECT EmployeeID, Department, Position FROM employee";
$result_employees = $conn->query($sql_employees);

if (!$result_employees) {
    die("Error fetching employees: " . $conn->error);
}

// Reset leave balances for each employee
while ($employee = $result_employees->fetch_assoc()) {
    $employee_id = $employee['EmployeeID'];
    $department = $employee['Department'];
    $position = $employee['Position'];

    // Fetch leave policies for the employee's department and position
    $sql_policies = "SELECT leave_type, allowed_days 
                     FROM leave_policy 
                     WHERE department = ? AND position = ?";
    $stmt_policies = $conn->prepare($sql_policies);
    if (!$stmt_policies) {
        die("Error preparing policies query: " . $conn->error);
    }
    $stmt_policies->bind_param("ss", $department, $position);
    $stmt_policies->execute();
    $result_policies = $stmt_policies->get_result();

    if (!$result_policies) {
        die("Error fetching policies: " . $conn->error);
    }

    while ($policy = $result_policies->fetch_assoc()) {
        $leave_type = $policy['leave_type'];
        $allowed_days = $policy['allowed_days'];

        // Check if a balance already exists for this year
        $sql_check = "SELECT * FROM leave_balances 
                      WHERE user_id = ? AND leave_type = ? AND year = ?";
        $stmt_check = $conn->prepare($sql_check);
        if (!$stmt_check) {
            die("Error preparing check query: " . $conn->error);
        }
        $stmt_check->bind_param("iii", $employee_id, $leave_type, $current_year);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if (!$result_check) {
            die("Error checking balances: " . $conn->error);
        }

        if ($result_check->num_rows === 0) {
            // Insert new leave balance for the current year
            $sql_insert = "INSERT INTO leave_balances (user_id, leave_type, used_days, remaining_days, total_days, year)
                           VALUES (?, ?, 0, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            if (!$stmt_insert) {
                die("Error preparing insert query: " . $conn->error);
            }

            // Set remaining_days and total_days to the allowed_days from the policy
            $remaining_days = $allowed_days;
            $total_days = $allowed_days;

            // Bind the variables to the placeholders
            $stmt_insert->bind_param("iiiii", $employee_id, $leave_type, $remaining_days, $total_days, $current_year);
            if (!$stmt_insert->execute()) {
                die("Error inserting balance: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        } else {
            // Update existing leave balance for the current year
            $sql_update = "UPDATE leave_balances 
                           SET used_days = 0, 
                               remaining_days = ?, 
                               total_days = ? 
                           WHERE user_id = ? AND leave_type = ? AND year = ?";
            $stmt_update = $conn->prepare($sql_update);
            if (!$stmt_update) {
                die("Error preparing update query: " . $conn->error);
            }

            // Set remaining_days and total_days to the allowed_days from the policy
            $remaining_days = $allowed_days;
            $total_days = $allowed_days;

            // Bind the variables to the placeholders
            $stmt_update->bind_param("iiiii", $remaining_days, $total_days, $employee_id, $leave_type, $current_year);
            if (!$stmt_update->execute()) {
                die("Error updating balance: " . $stmt_update->error);
            }
            $stmt_update->close();
        }

        $stmt_check->close();
    }

    $stmt_policies->close();
}

// Redirect to admin.php with a success message
header("Location: admin.php?message=Leave+balances+reset+successfully+for+the+year+$current_year.");
exit();
?>