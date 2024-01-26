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

// Fetch all job titles with department names
$allJobTitlesSql = "SELECT j.job_title_id, j.job_title_name, d.department_name 
                    FROM job_titles j
                    LEFT JOIN departments d ON j.department_id = d.department_id ORDER BY department_name ASC";
$allJobTitlesResult = $conn->query($allJobTitlesSql);

// Check if the query was successful
if (!$allJobTitlesResult) {
    die("Query failed: " . $conn->error);
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Job Titles - LOGICALIS SA</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation Bar -->
<?php include 'navbar.php'; ?>

  <!-- Main Content -->
  <div class="container mt-5" 
    style="background-color: white; border: solid black 2px; color: black; text-align: center; padding-top: 15px; border-radius: 20px;">
    <h2>LIST JOB TITLES</h2>
    <hr>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th scope="col">Job Title ID</th>
                <th scope="col">Department Name</th>
                <th scope="col">Job Title Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if there are results before attempting to fetch
            if ($allJobTitlesResult->num_rows > 0) {
                // Display all job titles with department names in the table
                while ($jobTitleRow = $allJobTitlesResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $jobTitleRow['job_title_id'] . '</td>';
                    echo '<td>' . $jobTitleRow['department_name'] . '</td>';
                    echo '<td>' . $jobTitleRow['job_title_name'] . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="3">No job titles found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>