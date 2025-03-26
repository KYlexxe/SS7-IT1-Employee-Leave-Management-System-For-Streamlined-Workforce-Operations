<?php  
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'dbconnect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch EmployeeID from users table
$query = "SELECT EmployeeID FROM employee WHERE Email = (SELECT email FROM users WHERE userid = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($employee_id);
$stmt->fetch();
$stmt->close();

// Filter by leave status
$statusFilter = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : '';

// Fetch leave history with LeaveName instead of LeaveTypeID
$sql = "SELECT leaverequest.RequestID, leavetypes.LeaveName, leaverequest.StartDate, 
               leaverequest.EndDate, leaverequest.Status 
        FROM leaverequest
        JOIN leavetypes ON leaverequest.leave_type = leavetypes.LeaveTypeID
        WHERE leaverequest.EmployeeID = ?";

if ($statusFilter) {
    $sql .= " AND leaverequest.Status = ?";
}

$sql .= " ORDER BY leaverequest.StartDate DESC";

$stmt = $conn->prepare($sql);

if ($statusFilter) {
    $stmt->bind_param("is", $employee_id, $statusFilter);
} else {
    $stmt->bind_param("i", $employee_id);
}

$stmt->execute();
$result = $stmt->get_result();

// Function for status badge colors
function getStatusBadge($status) {
    switch ($status) {
        case "Approved":
            return "<span class='badge bg-success'>Approved</span>";
        case "Pending":
            return "<span class='badge bg-warning text-dark'>Pending</span>";
        case "Rejected":
            return "<span class='badge bg-danger'>Rejected</span>";
        default:
            return "<span class='badge bg-secondary'>Unknown</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History</title>
    <link rel="stylesheet" href="bootstrap1.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="bg-dark text-white p-3 vh-100" style="width: 250px;">
        <h2 class="h5">Leave History</h2>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="user.php" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item"><a href="apply_leave.php" class="nav-link text-white">Apply Leave</a></li>
                <li class="nav-item"><a href="userprofile.php" class="nav-link text-white">Profile</a></li>
                <li class="nav-item"><a href="notifications.php" class="nav-link text-white">Notifications</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-danger">Logout</a></li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <div class="container mt-4">
            <h2>Leave History</h2>
            <div class="mb-3">
                <form method="GET" class="d-flex gap-2">
                    <label for="status" class="me-2 align-self-center">Filter by Status:</label>
                    <select name="status" id="status" class="form-select w-auto">
                        <option value="">All</option>
                        <option value="Approved" <?php if ($statusFilter == "Approved") echo "selected"; ?>>Approved</option>
                        <option value="Pending" <?php if ($statusFilter == "Pending") echo "selected"; ?>>Pending</option>
                        <option value="Rejected" <?php if ($statusFilter == "Rejected") echo "selected"; ?>>Rejected</option>
                    </select>
                    <button type="submit" class="btn btn-dark">Filter</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['LeaveName']); ?></td>
                            <td><?php echo htmlspecialchars($row['StartDate']); ?></td>
                            <td><?php echo htmlspecialchars($row['EndDate']); ?></td>
                            <td><?php echo getStatusBadge($row['Status']); ?></td>
                            <td>
                                <?php if ($row['Status'] == "Pending") { ?>
                                    <form action="cancelleave.php" method="POST" class="d-inline">
                                        <input type="hidden" name="request_id" value="<?php echo $row['RequestID']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                    </form>
                                <?php } else { ?>
                                    <span class="text-muted">N/A</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="bootstrap.bundle.min.js"></script>
</body>
</html>
