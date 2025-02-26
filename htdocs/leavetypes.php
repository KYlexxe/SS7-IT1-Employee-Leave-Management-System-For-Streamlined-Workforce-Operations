<?php
include 'dbconnect.php';
session_start();

        // Fetch leave types details
$sql_leave_types_details = "SELECT * FROM leavetypes";
$result_leave_types_details = mysqli_query($conn, $sql_leave_types_details);
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Types</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header>
        <h1>Leave Types</h1>
    </header>
    <nav>
        <ul>
        <li><a href="admin.php">Admin</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="employee.php">Employees</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="auditlog.php">Audit Log</a></li>
            <li><a href="calendar.php">Calendar</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="form-container">
    <main>
        <section>
            <h2>Leave Types List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
        <?php
                if (mysqli_num_rows($result_leave_types_details) > 0) {
                        while ($row = mysqli_fetch_assoc($result_leave_types_details)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['LeaveType']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>No leave types available.</td></tr>";
                    }
                    ?>
                    </tbody>
            </table>
        </section>
    </main>
                </div>
</body>
</html>