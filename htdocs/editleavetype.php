<?php
include 'dbconnect.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Validate if the ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid leave type ID.";
    header("Location: leavetypes.php");
    exit();
}

$leaveID = $_GET['id'];

// Fetch the leave type details
$sql = "SELECT * FROM leavetypes WHERE LeaveTypeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $leaveID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    $_SESSION['message'] = "Leave type not found.";
    header("Location: leavetypes.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leaveName = $_POST['leaveName'];
    $description = $_POST['description'];

    $sql = "UPDATE leavetypes SET LeaveName = ?, Description = ? WHERE LeaveTypeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $leaveName, $description, $leaveID);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Leave type updated successfully!";
        header("Location: leavetypes.php");
        exit();
    } else {
        $_SESSION['message'] = "Error updating leave type.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Leave Type</title>
    <link rel="stylesheet" href="bootstrap1.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="all.css">
    <script src="bootstrapbundle.js"></script>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container mt-4">
    <h2 class="mb-3">Edit Leave Type</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-info">
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Leave Name:</label>
                    <input type="text" name="leaveName" class="form-control" value="<?= htmlspecialchars($row['LeaveName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description:</label>
                    <textarea name="description" class="form-control" required><?= htmlspecialchars($row['Description']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="leavetypes.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
