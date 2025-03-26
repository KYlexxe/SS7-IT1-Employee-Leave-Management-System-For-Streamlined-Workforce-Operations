<?php
include 'dbconnect.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle adding new leave type
if (isset($_POST['add_leave'])) {
    $leaveName = $_POST['leaveName'];
    $description = $_POST['description'];

    $sql = "INSERT INTO leavetypes (LeaveName, Description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $leaveName, $description);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Leave type added successfully!";
    } else {
        $_SESSION['message'] = "Error adding leave type.";
    }

    header("Location: leavetypes.php");
    exit();
}

// Fetch leave types
$sql = "SELECT LeaveTypeID, LeaveName, Description FROM leavetypes";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Types</title>
    <link rel="stylesheet" href="bootstrap1.css">
    <link rel="stylesheet" href="sidebar.css">
    <script src="bootstrapbundle.js"></script>
    <style>
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container mt-4">
    <h2 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Manage Leave Types</h2>

    <!-- Add Leave Type Form -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
        <i class="fas fa-plus-circle me-1"></i> Add Leave Type
    </button>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th><i class="fas fa-tag me-1"></i>Leave Type</th>
                        <th><i class="fas fa-align-left me-1"></i>Description</th>
                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['LeaveName']) ?></td>
                                <td><?= htmlspecialchars($row['Description']) ?></td>
                                <td>
                                    <a href="editleavetype.php?id=<?= htmlspecialchars($row['LeaveTypeID']) ?>" 
                                       class="btn btn-warning btn-sm me-1">
                                       <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="deleteleavetype.php?id=<?= htmlspecialchars($row['LeaveTypeID']) ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this leave type?');">
                                       <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">
                                <i class="fas fa-info-circle me-1"></i>No leave types available.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Leave Modal -->
<div class="modal fade" id="addLeaveModal" tabindex="-1" aria-labelledby="addLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Leave Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="leaveName" class="form-label">
                            <i class="fas fa-tag me-1"></i>Leave Type
                        </label>
                        <input type="text" name="leaveName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Description
                        </label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="add_leave" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>