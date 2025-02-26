<?php
include 'dbconnect.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Query to fetch approved leave requests count grouped by leave type
$query = "SELECT leave_type, COUNT(*) AS total_approved 
          FROM leaverequest 
          WHERE Status = 'Approved' 
          GROUP BY leave_type";
$result = mysqli_query($conn, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
mysqli_free_result($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Reports Dashboard</title>
  <link rel="stylesheet" href="css.css">
  <!-- Include Chart.js from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
      /* Inline styles for demonstration; move these to css.css if needed */
      .container {
          width: 90%;
          max-width: 1100px;
          margin: 20px auto;
          padding: 20px;
          background: #fff;
          border-radius: 6px;
          box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }
  </style>
</head>
<body>
  <header>
      <h1>Admin Reports Dashboard</h1>
  </header>
  <nav>
      <ul> 
            <li><a href="admin.php">Admin</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="employee.php">Employees</a></li>
            <li><a href="leavetypes.php">Leave Types</a></li>
            <li><a href="auditlog.php">Audit Log</a></li>
            <li><a href="calendar.php">Calendar</a></li>
            <li><a href="logout.php">Logout</a></li>
      </ul>
  </nav>
  <div class="container">
      <h2>Approved Leave Requests by Type</h2>
      <canvas id="leaveChart" width="400" height="200"></canvas>
  </div>
  
  <script>
      // Convert PHP data to JavaScript
      const data = <?php echo json_encode($data); ?>;
      const labels = data.map(item => item.leave_type);
      const counts = data.map(item => parseInt(item.total_approved));
      
      const ctx = document.getElementById('leaveChart').getContext('2d');
      const leaveChart = new Chart(ctx, {
          type: 'bar',
          data: {
              labels: labels,
              datasets: [{
                  label: 'Total Approved Leaves',
                  data: counts,
                  backgroundColor: 'rgba(52, 152, 219, 0.7)',
                  borderColor: 'rgba(41, 128, 185, 1)',
                  borderWidth: 1
              }]
          },
          options: {
              scales: {
                  y: {
                      beginAtZero: true,
                      ticks: {
                          precision: 0
                      }
                  }
              }
          }
      });
  </script>
</body>
</html>
