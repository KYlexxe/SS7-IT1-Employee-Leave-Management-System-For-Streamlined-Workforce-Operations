<?php
include 'dbconnect.php';
session_start();

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch Approved Leave Requests
$query_approved = "SELECT leave_type, COUNT(*) AS total_approved FROM leaverequest WHERE Status = 'Approved' GROUP BY leave_type";
$result_approved = mysqli_query($conn, $query_approved);
$approved_data = mysqli_fetch_all($result_approved, MYSQLI_ASSOC);
mysqli_free_result($result_approved);

// Fetch Rejected Leave Requests
$query_rejected = "SELECT leave_type, COUNT(*) AS total_rejected FROM leaverequest WHERE Status = 'Rejected' GROUP BY leave_type";
$result_rejected = mysqli_query($conn, $query_rejected);
$rejected_data = mysqli_fetch_all($result_rejected, MYSQLI_ASSOC);
mysqli_free_result($result_rejected);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Reports</title>
    <link rel="stylesheet" href="csss.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {packages: ['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // Approved Leave Requests Chart
            var approvedData = new google.visualization.DataTable();
            approvedData.addColumn('string', 'Leave Type');
            approvedData.addColumn('number', 'Total Approved');
            approvedData.addRows([
                <?php foreach ($approved_data as $row) {
                    echo "['" . addslashes($row['leave_type']) . "', " . $row['total_approved'] . "],";
                } ?>
            ]);

            var approvedOptions = {
                title: 'Approved Leave Requests by Type',
                hAxis: { title: 'Leave Type' },
                vAxis: { title: 'Total Approved' },
                bars: 'vertical',
                colors: ['#1b9e77']
            };

            var approvedChart = new google.visualization.ColumnChart(document.getElementById('approved_chart'));
            approvedChart.draw(approvedData, approvedOptions);

            // Rejected Leave Requests Chart
            var rejectedData = new google.visualization.DataTable();
            rejectedData.addColumn('string', 'Leave Type');
            rejectedData.addColumn('number', 'Total Rejected');
            rejectedData.addRows([
                <?php foreach ($rejected_data as $row) {
                    echo "['" . addslashes($row['leave_type']) . "', " . $row['total_rejected'] . "],";
                } ?>
            ]);

            var rejectedOptions = {
                title: 'Rejected Leave Requests by Type',
                hAxis: { title: 'Leave Type' },
                vAxis: { title: 'Total Rejected' },
                bars: 'vertical',
                colors: ['#e74c3c']
            };

            var rejectedChart = new google.visualization.ColumnChart(document.getElementById('rejected_chart'));
            rejectedChart.draw(rejectedData, rejectedOptions);
        }
    </script>
</head>
<body>

<div class="sidebar">
    <h2>Reports</h2>
    <ul>
        <li><a href="admin.php">Dashboard</a></li>
        <li><a href="employee.php">Employees</a></li>
        <li><a href="leavetypes.php">Leave Types</a></li>
        <li><a href="auditlog.php">Audit Log</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

    <div class="container">
        <h1>Leave Reports</h1>
        <div class="chart-container">
            <div id="approved_chart" class="chart"></div>
            <div id="rejected_chart" class="chart"></div>
        </div>
    </div>

</body>
</html>
