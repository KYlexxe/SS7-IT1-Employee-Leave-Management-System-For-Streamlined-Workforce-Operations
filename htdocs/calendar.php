<?php
include 'dbconnect.php';
session_start();

// Optionally, check that the user is an admin (if only HR should view the calendar)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Query approved leave requests (join with employee table to get employee name)
$sql = "SELECT lr.RequestID, lr.leave_type, lr.StartDate, lr.EndDate, e.Name AS EmployeeName 
        FROM leaverequest lr
        JOIN employee e ON lr.EmployeeID = e.EmployeeID
        WHERE lr.Status = 'Approved'";
$result = mysqli_query($conn, $sql);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    // FullCalendar expects ISO 8601 date strings. Adjust the title as desired.
    $events[] = [
        'title' => $row['EmployeeName'] . " - " . $row['leave_type'],
        'start' => $row['StartDate'],
        'end'   => $row['EndDate']  // If EndDate should be exclusive, you might need to adjust it.
    ];
}

$events_json = json_encode($events);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Approved Leave Calendar</title>
  <!-- FullCalendar CSS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <!-- Custom CSS (if any) -->
  <link rel="stylesheet" href="css.css">
  <style>
    /* Optional inline styles for calendar container */
    #calendar {
      max-width: 900px;
      margin: 40px auto;
      background: #fff;
      padding: 20px;
      border-radius: 6px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <header>
    <h1>Approved Leave Calendar</h1>
  </header>
  <nav>
    <ul>
      <li><a href="admin.php">Admin</a></li>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="employee.php">Employees</a></li>
      <li><a href="leavetypes.php">Leave Types</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
  
  <div id="calendar"></div>
  
  <!-- FullCalendar JS -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');
      
      // Get events from PHP (decoded from JSON)
      var events = <?php echo $events_json; ?>;
      
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: events,
        eventClick: function(info) {
          // Optional: display more details on event click
          alert(info.event.title + "\nStart: " + info.event.start.toISOString().split("T")[0] + "\nEnd: " + info.event.end.toISOString().split("T")[0]);
        }
      });
      
      calendar.render();
    });
  </script>
</body>
</html>
