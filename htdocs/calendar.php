<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'dbconnect.php'; 

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) != 'admin') {
    header("Location: login.php");
    exit();
}

// Get current month and year from user input or default to current
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fetch leave data (only approved requests)
$query = "
    SELECT e.Name, l.StartDate, l.EndDate, lt.LeaveName 
    FROM leaverequest l 
    JOIN employee e ON l.EmployeeID = e.EmployeeID 
    JOIN leavetypes lt ON l.leave_type = lt.LeaveTypeID 
    WHERE l.Status = 'Approved'
";

$result = $conn->query($query);
$leaveData = [];
while ($row = $result->fetch_assoc()) {
    $name = $row['Name'];
    $leaveType = $row['LeaveName'];
    $start = strtotime($row['StartDate']);
    $end = strtotime($row['EndDate']);

    for ($date = $start; $date <= $end; $date += 86400) {
        $formattedDate = date('Y-m-d', $date);
        $leaveData[$formattedDate][] = "$name - $leaveType";
    }
}

function buildCalendar($month, $year, $leaveData) {
    $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $totalDays = date('t', $firstDay);
    $startDay = date('N', $firstDay);

    echo "<table class='table table-bordered text-center'>";
    echo "<thead class='table-dark'><tr>";
    foreach ($daysOfWeek as $day) {
        echo "<th>$day</th>";
    }
    echo "</tr></thead><tbody><tr>";

    for ($i = 1; $i < $startDay; $i++) {
        echo "<td class='bg-light'></td>";
    }

    for ($day = 1; $day <= $totalDays; $day++) {
        $currentDate = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
        $isLeaveDay = isset($leaveData[$currentDate]) ? "table-danger" : "";

        echo "<td class='$isLeaveDay'><strong>$day</strong>";

        if (isset($leaveData[$currentDate])) {
            echo "<ul class='list-unstyled mt-1 text-start'>";
            foreach ($leaveData[$currentDate] as $info) {
                echo "<li class='badge bg-warning text-dark d-block mb-1'>$info</li>";
            }
            echo "</ul>";
        }
        echo "</td>";

        if (date('N', mktime(0, 0, 0, $month, $day, $year)) == 7) {
            echo "</tr><tr>";
        }
    }
    echo "</tr></tbody></table>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Calendar</title>
    <link rel="stylesheet" href="sidebar.css">
    
    <link rel="stylesheet" href="bootstrap1.css">
</head>
<body>
    
    <?php include 'sidebar.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-calendar-alt"></i> Approved Leave Calendar</h2>
        </div>

        <div class="d-flex justify-content-between mb-3">
            <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>" class="btn btn-primary">&lt; Prev</a>
            <h3><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h3>
            <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>" class="btn btn-primary">Next &gt;</a>
        </div>

        <form method="GET" class="mb-3 d-flex gap-2">
            <select name="month" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-select">
                <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-success">Go</button>
        </form>

        <?php buildCalendar($month, $year, $leaveData); ?>
    </div>
</body>
</html>