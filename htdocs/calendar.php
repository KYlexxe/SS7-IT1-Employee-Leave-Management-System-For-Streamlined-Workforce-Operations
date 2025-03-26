<?php
include 'dbconnect.php'; 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get current month and year from user input or default to current
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fetch leave data
$query = "SELECT e.Name, l.StartDate, l.EndDate, l.leave_type 
          FROM leaverequest l 
          JOIN employee e ON l.EmployeeID = e.EmployeeID";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$leaveData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $name = $row['Name'];
    $leaveType = $row['leave_type'];
    $start = strtotime($row['StartDate']);
    $end = strtotime($row['EndDate']);

    for ($date = $start; $date <= $end; $date += 86400) {
        $formattedDate = date('Y-m-d', $date);
        $leaveData[$formattedDate]["$name - $leaveType"] = true;
    }
}

function buildCalendar($month, $year, $leaveData) {
    $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $totalDays = date('t', $firstDay);
    $startDay = date('N', $firstDay);

    echo "<table style='width: 100%; border-collapse: collapse; text-align: center;'>";
    echo "<tr>";
    foreach ($daysOfWeek as $day) {
        echo "<th style='background: #007BFF; color: white; padding: 10px; border: 1px solid #ddd;'>$day</th>";
    }
    echo "</tr><tr>";

    for ($i = 1; $i < $startDay; $i++) {
        echo "<td style='background: #f4f4f4; border: 1px solid #ddd;'></td>";
    }

    for ($day = 1; $day <= $totalDays; $day++) {
        $currentDate = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
        $isLeaveDay = isset($leaveData[$currentDate]) ? "background: #ffebeb;" : "";

        echo "<td style='border: 1px solid #ddd; padding: 10px; height: 80px; vertical-align: top; $isLeaveDay'>";
        echo "<strong>$day</strong>";

        if (isset($leaveData[$currentDate])) {
            echo "<div style='margin-top: 5px; font-size: 12px; color: #d9534f; text-align: left;'>";
            foreach (array_keys($leaveData[$currentDate]) as $info) {
                echo "<div style='padding: 3px; background: #f8d7da; border-radius: 5px; margin: 2px 0;'>$info</div>";
            }
            echo "</div>";
        }

        echo "</td>";

        if (date('N', mktime(0, 0, 0, $month, $day, $year)) == 7) {
            echo "</tr><tr>";
        }
    }

    echo "</tr></table>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Calendar</title>
    <link rel="stylesheet" href="calendar.css"> <!-- External CSS Linked -->
</head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0;">

<!-- Sidebar -->
<div class="sidebar">
    <h2>Approved Calendar</h2>
    <ul>
        <li><a href="admin.php">Dashboard</a></li>
        <li><a href="employee.php">Employees</a></li>
        <li><a href="leavetypes.php">Leave Types</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="auditlog.php">Audit Log</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="content">
    <div class="calendar-container">
        <div class="nav-links">
            <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>">&lt; Prev</a>
            <h2><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h2>
            <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>">Next &gt;</a>
        </div>

        <form method="GET">
            <select name="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                <?php endfor; ?>
            </select>
            <select name="year">
                <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit">Go</button>
        </form>

        <?php buildCalendar($month, $year, $leaveData); ?>
    </div>
</div>

</body>
</html>
