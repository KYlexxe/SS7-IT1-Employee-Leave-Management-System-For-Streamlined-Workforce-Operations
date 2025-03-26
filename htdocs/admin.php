<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'dbconnect.php';

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) != 'admin') {
    header("Location: login.php");
    exit();
}

/**
 * Sends a notification to a user after verifying the user exists
 */
function sendNotification($conn, $user_id, $message) {
    $check = $conn->prepare("SELECT userid FROM users WHERE userid = ?");
    if (!$check) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $check->bind_param("i", $user_id);
    if (!$check->execute()) {
        error_log("Execute failed: " . $check->error);
        $check->close();
        return false;
    }
    
    $check->store_result();
    
    if ($check->num_rows === 0) {
        error_log("Notification failed: User $user_id doesn't exist");
        $check->close();
        return false;
    }
    $check->close();

    try {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("is", $user_id, $message);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
        return false;
    }
}

// Fetch pending leave requests
$sql = "SELECT lr.RequestID, e.EmployeeID, e.user_id, e.name, lt.LeaveName, 
               lr.StartDate, lr.EndDate, lr.days_requested, lr.Status, lr.remarks 
        FROM leaverequest lr 
        JOIN employee e ON lr.EmployeeID = e.EmployeeID 
        JOIN leavetypes lt ON lr.leave_type = lt.LeaveTypeID 
        WHERE lr.Status = 'Pending'";
$leave_requests = $conn->query($sql);

if (!$leave_requests) {
    die("Error fetching leave requests: " . $conn->error);
}

// Fetch pending users
$query = "SELECT userid, name, email FROM users WHERE status = 'Pending'";
$pending_users = $conn->query($query);

if (!$pending_users) {
    die("Error fetching pending users: " . $conn->error);
}

// Approve Leave Request - Full working version
if (isset($_POST['approve_leave'])) {
    $requestID = intval($_POST['requestID']);
    $status = 'Approved';

    $conn->begin_transaction();

    try {
        // Get all leave request details including employee info
        $sql = "SELECT lr.*, e.EmployeeID, e.user_id, e.department, e.position, 
                       lt.LeaveName, lt.LeaveTypeID,
                       COALESCE(lp.allowed_days, 
                           CASE 
                               WHEN lt.LeaveName = 'Vacation Leave' THEN 15
                               WHEN lt.LeaveName = 'Sick Leave' THEN 10
                               ELSE 5 
                           END) AS total_days
                FROM leaverequest lr
                JOIN employee e ON lr.EmployeeID = e.EmployeeID
                JOIN leavetypes lt ON lr.leave_type = lt.LeaveTypeID
                LEFT JOIN leave_policy lp ON 
                    lp.department = e.department AND 
                    lp.position = e.position AND 
                    lp.leave_type = lr.leave_type
                WHERE lr.RequestID = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("i", $requestID);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        $leave_request = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$leave_request) throw new Exception("Leave request not found");

        // Update leave request status
        $update_sql = "UPDATE leaverequest SET Status = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($update_sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("si", $status, $requestID);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        $stmt->close();

        // Use EmployeeID if no user_id exists
        $balance_user_id = !empty($leave_request['user_id']) ? $leave_request['user_id'] : $leave_request['EmployeeID'];

        // Check if balance exists for either user_id or EmployeeID
        $check_balance = "SELECT id FROM leave_balances 
                         WHERE user_id IN (?, ?) AND leave_type = ?";
        $stmt = $conn->prepare($check_balance);
        $stmt->bind_param("iii", 
            $leave_request['user_id'],
            $leave_request['EmployeeID'],
            $leave_request['leave_type']
        );
        $stmt->execute();
        $balance_result = $stmt->get_result();
        $balance_exists = $balance_result->num_rows > 0;
        $stmt->close();

        if ($balance_exists) {
            // Update existing balance
            $balance_sql = "UPDATE leave_balances 
                           SET used_days = used_days + ?, 
                               remaining_days = remaining_days - ? 
                           WHERE user_id IN (?, ?) AND leave_type = ?";
            $stmt = $conn->prepare($balance_sql);
            $stmt->bind_param("iiiii", 
                $leave_request['days_requested'],
                $leave_request['days_requested'],
                $leave_request['user_id'],
                $leave_request['EmployeeID'],
                $leave_request['leave_type']
            );
        } else {
            // Create new balance record
            $total_days = $leave_request['total_days'];
            $remaining = $total_days - $leave_request['days_requested'];
            
            $balance_sql = "INSERT INTO leave_balances 
                           (user_id, leave_type, used_days, remaining_days, total_days, year)
                           VALUES (?, ?, ?, ?, ?, YEAR(CURDATE()))";
            $stmt = $conn->prepare($balance_sql);
            $stmt->bind_param("iiiid", 
                $balance_user_id,
                $leave_request['leave_type'],
                $leave_request['days_requested'],
                $remaining,
                $total_days
            );
        }

        if (!$stmt->execute()) throw new Exception("Failed to update leave balance: " . $stmt->error);
        $stmt->close();

        // Add to audit log
        $audit_sql = "INSERT INTO audit_log (request_id, changed_by, change_message) 
                      VALUES (?, ?, ?)";
        $stmt = $conn->prepare($audit_sql);
        $message = "Leave approved";
        $stmt->bind_param("iis", $requestID, $_SESSION['user_id'], $message);
        $stmt->execute();
        $stmt->close();

        // Send notification only if user exists
        if (!empty($leave_request['user_id'])) {
            $message = "Your {$leave_request['LeaveName']} leave request (ID: $requestID) has been approved.";
            if (!sendNotification($conn, $leave_request['user_id'], $message)) {
                error_log("Failed to send notification for user {$leave_request['user_id']}");
            }
        }

        $conn->commit();
        header("Location: admin.php?message=Leave+approved+successfully");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: admin.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Reject Leave Request
if (isset($_POST['reject_leave'])) {
    $requestID = intval($_POST['requestID']);
    $rejectReason = $conn->real_escape_string($_POST['reject_reason']);

    $conn->begin_transaction();

    try {
        // Get user info if exists
        $userQuery = "SELECT u.userid, e.EmployeeID, e.name
                     FROM leaverequest lr
                     JOIN employee e ON lr.EmployeeID = e.EmployeeID
                     LEFT JOIN users u ON e.user_id = u.userid
                     WHERE lr.RequestID = ?";
        $stmt = $conn->prepare($userQuery);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("i", $requestID);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        $userRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$userRow) throw new Exception("Leave request not found");

        // Update leave request status
        $query = "UPDATE leaverequest SET Status = 'Rejected', remarks = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("si", $rejectReason, $requestID);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        $stmt->close();

        // Add to audit log
        $audit_sql = "INSERT INTO audit_log (request_id, changed_by, change_message) 
                      VALUES (?, ?, ?)";
        $stmt = $conn->prepare($audit_sql);
        $message = "Leave rejected: " . substr($rejectReason, 0, 100);
        $stmt->bind_param("iis", $requestID, $_SESSION['user_id'], $message);
        $stmt->execute();
        $stmt->close();

        // Send notification if user exists
        if (!empty($userRow['userid'])) {
            $message = "Your leave request (ID: $requestID) was rejected. Reason: $rejectReason";
            if (!sendNotification($conn, $userRow['userid'], $message)) {
                error_log("Failed to send rejection notification");
            }
        }

        $conn->commit();
        header("Location: admin.php?message=Leave+rejected+successfully");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: admin.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Approve User Account
if (isset($_POST['approve_user'])) {
    $user_id = intval($_POST['user_id']);
    
    try {
        $query = "UPDATE users SET status = 'Approved' WHERE userid = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        $stmt->close();

        $message = "Your account has been approved. You can now log in.";
        if (!sendNotification($conn, $user_id, $message)) {
            throw new Exception("Failed to send approval notification");
        }

        header("Location: admin.php?message=User+approved+successfully");
        exit();
    } catch (Exception $e) {
        header("Location: admin.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Reject User Account
if (isset($_POST['reject_user'])) {
    $user_id = intval($_POST['user_id']);
    
    $conn->begin_transaction();
    try {
        $message = "Your account registration has been rejected. Please contact support.";
        if (!sendNotification($conn, $user_id, $message)) {
            throw new Exception("Failed to send rejection notification");
        }

        $deleteNotifications = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
        if (!$deleteNotifications) throw new Exception("Prepare failed: " . $conn->error);
        $deleteNotifications->bind_param("i", $user_id);
        if (!$deleteNotifications->execute()) throw new Exception("Failed to delete notifications");
        $deleteNotifications->close();

        // Clean up related records
        $tables = ['leave_balances', 'employee', 'users'];
        foreach ($tables as $table) {
            $delete = $conn->prepare("DELETE FROM $table WHERE user_id = ?");
            if ($delete) {
                $delete->bind_param("i", $user_id);
                if (!$delete->execute()) error_log("Failed to delete from $table: " . $delete->error);
                $delete->close();
            }
        }

        $conn->commit();
        header("Location: admin.php?message=User+rejected+successfully");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: admin.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Reset Leave Balances
if (isset($_POST['reset_balances'])) {
    header("Location: resetleave_balances.php");
    exit();
}

// HTML portion would start here...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="bootstrap1.css" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="container mt-4">
        <!-- Display success/error messages -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Reset Leave Balances Button -->
        <div class="mb-4">
            <form action="admin.php" method="POST">
                <button type="submit" name="reset_balances" class="btn btn-warning">
                    Reset Leave Balances for New Year
                </button>
            </form>
        </div>

        <div class="row">
            <!-- Leave Requests -->
            <div class="col-md-6">
                <h2>Leave Requests</h2>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($leave_requests->num_rows > 0): ?>
                                <?php while ($row = $leave_requests->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['LeaveName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['StartDate']); ?></td>
                                    <td><?php echo htmlspecialchars($row['EndDate']); ?></td>
                                    <td><?php echo htmlspecialchars($row['days_requested']); ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="requestID" value="<?php echo $row['RequestID']; ?>">
                                            <button type="submit" name="approve_leave" class="btn btn-success btn-sm">Approve</button>
                                        </form>

                                        <!-- Button to Open Modal -->
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $row['RequestID']; ?>">
                                            Reject
                                        </button>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?php echo $row['RequestID']; ?>" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="rejectModalLabel">Reject Leave Request</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="requestID" value="<?php echo $row['RequestID']; ?>">
                                                            <label for="reject_reason" class="form-label">Reason for Rejection:</label>
                                                            <textarea name="reject_reason" class="form-control" required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="reject_leave" class="btn btn-danger">Reject</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No pending leave requests.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Users -->
            <div class="col-md-6">
                <h2>Pending User Approvals</h2>
                <div class="table-responsive">
                    <table class="table table-bordered bg-white">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pending_users->num_rows > 0): ?>
                                <?php while ($row = $pending_users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td>
                                            <form action="admin.php" method="POST" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo $row['userid']; ?>">
                                                <button type="submit" name="approve_user" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form action="admin.php" method="POST" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo $row['userid']; ?>">
                                                <button type="submit" name="reject_user" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No pending users at the moment.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrapbundle.js"></script>
</body>
</html>