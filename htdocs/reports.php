<?php
include 'dbconnect.php';
session_start();

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch Approved Leave Requests with Leave Name
$query_approved = "
    SELECT lt.LeaveName AS leave_name, COUNT(*) AS total_approved 
    FROM leaverequest lr
    JOIN leavetypes lt ON lr.leave_type = lt.LeaveTypeID
    WHERE lr.Status = 'Approved' 
    GROUP BY lt.LeaveName";
$result_approved = mysqli_query($conn, $query_approved);
$approved_data = mysqli_fetch_all($result_approved, MYSQLI_ASSOC);
mysqli_free_result($result_approved);

// Fetch Rejected Leave Requests with Leave Name
$query_rejected = "
    SELECT lt.LeaveName AS leave_name, COUNT(*) AS total_rejected 
    FROM leaverequest lr
    JOIN leavetypes lt ON lr.leave_type = lt.LeaveTypeID
    WHERE lr.Status = 'Rejected' 
    GROUP BY lt.LeaveName";
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
    <link href="bootstrap1.css" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    
    <script src="loader.js"></script>
    
    <script>
        google.charts.load('current', {packages: ['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // Chart for Approved Leave Requests
            var approvedData = new google.visualization.DataTable();
            approvedData.addColumn('string', 'Leave Type');
            approvedData.addColumn('number', 'Total Approved');
            approvedData.addRows([
                <?php foreach ($approved_data as $row) {
                    echo "['" . addslashes($row['leave_name']) . "', " . $row['total_approved'] . "],";
                } ?>
            ]);

            var approvedOptions = {
                title: 'Approved Leave Requests',
                colors: ['#1b9e77']
            };
            var approvedChart = new google.visualization.ColumnChart(document.getElementById('approved_chart'));
            approvedChart.draw(approvedData, approvedOptions);

            // Chart for Rejected Leave Requests
            var rejectedData = new google.visualization.DataTable();
            rejectedData.addColumn('string', 'Leave Type');
            rejectedData.addColumn('number', 'Total Rejected');
            rejectedData.addRows([
                <?php foreach ($rejected_data as $row) {
                    echo "['" . addslashes($row['leave_name']) . "', " . $row['total_rejected'] . "],";
                } ?>
            ]);

            var rejectedOptions = {
                title: 'Rejected Leave Requests',
                colors: ['#e74c3c']
            };
            var rejectedChart = new google.visualization.ColumnChart(document.getElementById('rejected_chart'));
            rejectedChart.draw(rejectedData, rejectedOptions);
        }
    </script>
    
    <!-- jsPDF and AutoTable for PDF Export -->
    <script src="jspdf.js"></script>
    <script src="plugin.js"></script>
    
    <script>
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.text("Leave Reports", 14, 10);
            
            let approvedData = [
                ["Leave Type", "Total Approved"]
            ];
            <?php foreach ($approved_data as $row): ?>
                approvedData.push(["<?php echo addslashes($row['leave_name']); ?>", <?php echo $row['total_approved']; ?>]);
            <?php endforeach; ?>

            let rejectedData = [
                ["Leave Type", "Total Rejected"]
            ];
            <?php foreach ($rejected_data as $row): ?>
                rejectedData.push(["<?php echo addslashes($row['leave_name']); ?>", <?php echo $row['total_rejected']; ?>]);
            <?php endforeach; ?>

            doc.autoTable({
                head: approvedData.slice(0, 1),
                body: approvedData.slice(1),
                startY: 20,
                theme: "grid",
                styles: { fontSize: 10 },
                headStyles: { fillColor: [27, 158, 119] },
            });

            doc.autoTable({
                head: rejectedData.slice(0, 1),
                body: rejectedData.slice(1),
                startY: doc.lastAutoTable.finalY + 10,
                theme: "grid",
                styles: { fontSize: 10 },
                headStyles: { fillColor: [231, 76, 60] },
            });

            doc.save("Leave_Reports.pdf");
        }
    </script>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container mt-4">
    <h1 class="text-center">Leave Reports</h1>
    <div class="row">
        <div class="col-md-6">
            <div id="approved_chart" style="width: 100%; height: 400px;"></div>
        </div>
        <div class="col-md-6">
            <div id="rejected_chart" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <div class="text-center mt-4">
        <button class="btn btn-primary" onclick="exportToPDF()">Export to PDF</button>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="bootstrapbundle.js"></script>
</body>
</html>
