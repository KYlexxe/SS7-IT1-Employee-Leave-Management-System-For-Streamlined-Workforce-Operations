<?php
// Include database connection
include 'dbconnect.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if 'id' parameter is set and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Employee ID!");
}

$employee_id = intval($_GET['id']);

// Fetch employee details from the database
$sql = "SELECT EmployeeID, name, position, department FROM employee WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    die("Employee not found!");
}

// Close connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2 class="mb-3">Edit Employee Details</h2>

    <form action="updateemployee.php" method="POST" class="card p-4 shadow">
        <input type="hidden" name="id" value="<?php echo $row['EmployeeID']; ?>">

        <div class="mb-3">
            <label for="name" class="form-label">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="position" class="form-label">Position:</label>
            <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($row['position']); ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="department" class="form-label">Department:</label>
            <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($row['department']); ?>" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Employee</button>
        <a href="employee_list.php" class="btn btn-secondary">Cancel</a>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
