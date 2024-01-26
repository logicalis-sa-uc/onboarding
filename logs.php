<?php
// Check if the session is not already started
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION['uName']) || empty($_SESSION['uName'])) {
    header("Location: onboarding-login.php");
    exit();
}
// Include the database configuration
include 'config.php';

// Fetch all logs from the 'lsa_logs' table
$allLogsSql = "SELECT * FROM lsa_logs ORDER BY logDate DESC";
$allLogsResult = $conn->query($allLogsSql);

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - LOGICALIS SA</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Main Content -->
<div class="container mt-5" 
    style="background-color: white; border: solid black 2px; color: black; text-align: center; padding-top: 15px; border-radius: 20px;">
    <h2>LOGS</h2>
    <hr>
      <?php
      // Display logs, if any
      if ($allLogsResult->num_rows > 0) {
        echo '<table class="table table-striped table-bordered">';
        echo '<thead class="thead-dark">';
        echo '<tr>';
        echo '<th scope="col">Log ID</th>';
        echo '<th scope="col">Date</th>';
        echo '<th scope="col">User</th>';
        echo '<th scope="col">Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        while ($logRow = $allLogsResult->fetch_assoc()) {
            echo '<tr>';
            echo '<th scope="row">' . $logRow['log_id'] . '</th>';
            echo '<td>' . $logRow['logDate'] . '</td>';
            echo '<td>' . $logRow['uName'] . '</td>';
            echo '<td>' . $logRow['logAction'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
      } else {
        echo '<div class="alert alert-info" role="alert">';
        echo 'No logs found.';
        echo '</div>';
      }
      ?>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
