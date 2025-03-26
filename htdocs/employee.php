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

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $employee_id_to_delete = $_GET['delete'];
    
    // Prepare the DELETE query
    $delete_sql = "DELETE FROM employee WHERE EmployeeID = ?";
    $stmt_delete = $conn->prepare($delete_sql);
    $stmt_delete->bind_param("i", $employee_id_to_delete);
    
    if ($stmt_delete->execute()) {
        $message = "Employee deleted successfully.";
        header("Location: employee.php?success=Employee deleted successfully");
        exit();
    } else {
        $message = "Error deleting employee: " . $stmt_delete->error;
    }
    
    $stmt_delete->close();
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
            header("Location: employee.php?success=Employee details updated successfully");
            exit();
        } else {
            $message = "Error updating employee details: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
    
    // Fetch current employee details for editing.
    $sql = "SELECT EmployeeID, name, email, position, department, Hire_Date FROM employee WHERE EmployeeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();
}

// Handle search and filter
$search = $_GET['search'] ?? '';
$filter_department = $_GET['department'] ?? '';
$filter_position = $_GET['position'] ?? '';

// Base query
$list_sql = "SELECT EmployeeID, name, email, position, department, Hire_Date FROM employee WHERE 1=1";

// Add search filter
if (!empty($search)) {
    $list_sql .= " AND (name LIKE ? OR email LIKE ? OR position LIKE ? OR department LIKE ?)";
}

// Add department filter
if (!empty($filter_department)) {
    $list_sql .= " AND department = ?";
}

// Add position filter
if (!empty($filter_position)) {
    $list_sql .= " AND position = ?";
}

// Prepare and execute the query
$stmt_list = $conn->prepare($list_sql);

if (!empty($search)) {
    $search_term = "%$search%";
    if (!empty($filter_department) && !empty($filter_position)) {
        $stmt_list->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $filter_department, $filter_position);
    } elseif (!empty($filter_department)) {
        $stmt_list->bind_param("ssss", $search_term, $search_term, $search_term, $search_term, $filter_department);
    } elseif (!empty($filter_position)) {
        $stmt_list->bind_param("ssss", $search_term, $search_term, $search_term, $search_term, $filter_position);
    } else {
        $stmt_list->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    }
} else {
    if (!empty($filter_department) && !empty($filter_position)) {
        $stmt_list->bind_param("ss", $filter_department, $filter_position);
    } elseif (!empty($filter_department)) {
        $stmt_list->bind_param("s", $filter_department);
    } elseif (!empty($filter_position)) {
        $stmt_list->bind_param("s", $filter_position);
    }
}

$stmt_list->execute();
$result_list = $stmt_list->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Management</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="bootstrap1.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="container mt-4">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($message)): ?>
            <div class="alert <?php echo (strpos($message, 'successfully') !== false) ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message ?? ''); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($employee)): ?>
            <h2 class="mb-4">Edit Employee Details</h2>
            <form method="POST" class="card p-4 shadow">
                <input type="hidden" name="employee_id" value="<?php echo $employee['EmployeeID']; ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($employee['name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="position" class="form-label">Position:</label>
                    <input type="text" class="form-control" id="position" name="position" value="<?php echo htmlspecialchars($employee['position'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="department" class="form-label">Department:</label>
                    <input type="text" class="form-control" id="department" name="department" value="<?php echo htmlspecialchars($employee['department'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="hire_date" class="form-label">Hire Date:</label>
                    <input type="date" class="form-control" id="hire_date" name="hire_date" value="<?php echo htmlspecialchars($employee['Hire_Date'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update Employee</button>
                <a href="employee.php" class="btn btn-secondary">Back to Employee List</a>
            </form>
        <?php else: ?>
            <h2 class="mb-4">Employee List</h2>

            <!-- Search Bar -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search by Name, Email, Position, or Department" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="employee.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <!-- Filters -->
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label for="department" class="form-label">Filter by Department:</label>
                        <select class="form-control" id="department" name="department">
                            <option value="">All Departments</option>
                            <?php
                            $departments_sql = "SELECT DISTINCT department FROM employee";
                            $departments_result = mysqli_query($conn, $departments_sql);
                            while ($dept = mysqli_fetch_assoc($departments_result)) {
                                $selected = ($filter_department == $dept['department']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($dept['department']) . "' $selected>" . htmlspecialchars($dept['department']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="position" class="form-label">Filter by Position:</label>
                        <select class="form-control" id="position" name="position">
                            <option value="">All Positions</option>
                            <?php
                            $positions_sql = "SELECT DISTINCT position FROM employee";
                            $positions_result = mysqli_query($conn, $positions_sql);
                            while ($pos = mysqli_fetch_assoc($positions_result)) {
                                $selected = ($filter_position == $pos['position']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($pos['position']) . "' $selected>" . htmlspecialchars($pos['position']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </div>
                </div>
            </form>

            <!-- Employee Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Hire Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_list->num_rows > 0) {
                            while ($emp = $result_list->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($emp['name'] ?? '') . "</td>";
                                echo "<td>" . htmlspecialchars($emp['email'] ?? '') . "</td>";
                                echo "<td>" . htmlspecialchars($emp['position'] ?? '') . "</td>";
                                echo "<td>" . htmlspecialchars($emp['department'] ?? '') . "</td>";
                                echo "<td>" . htmlspecialchars($emp['Hire_Date'] ?? '') . "</td>";
                                echo "<td><a href='employee.php?edit=" . htmlspecialchars($emp['EmployeeID']) . "' class='btn btn-warning btn-sm'>Edit</a>&nbsp;";
                                echo "<a href='employee.php?delete=" . htmlspecialchars($emp['EmployeeID']) . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure you want to delete this employee?');\">Delete</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No employees found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
