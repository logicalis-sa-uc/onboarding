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

// Initialize variables to store user input
$jobTitleId = '';

// Initialize an array to store validation errors
$errors = array();

// Initialize a variable to track success
$success = false;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user input
    $jobTitleId = isset($_POST['selectJobTitle']) ? $_POST['selectJobTitle'] : '';

    // Check if the job title exists in the database
    $checkJobTitleSql = "SELECT * FROM job_titles WHERE job_title_id = ?";
    $checkJobTitleStmt = $conn->prepare($checkJobTitleSql);
    $checkJobTitleStmt->bind_param('i', $jobTitleId);
    $checkJobTitleStmt->execute();
    $checkJobTitleResult = $checkJobTitleStmt->get_result();

    if ($checkJobTitleResult->num_rows === 0) {
        $errors[] = 'Job Title not found.';
    } else {
        // Remove the job title from the 'job_titles' table
        $removeJobTitleSql = "DELETE FROM job_titles WHERE job_title_id = ?";
        $removeJobTitleStmt = $conn->prepare($removeJobTitleSql);
        $removeJobTitleStmt->bind_param('i', $jobTitleId);
        $removeJobTitleStmt->execute();
        $removeJobTitleStmt->close();

        // Set success to true
        $success = true;

        // Insert log information
        $logAction = "Removed Job Title with ID: $jobTitleId";
        $logSql = "INSERT INTO lsa_logs (uName, logAction) VALUES ('{$_SESSION['uName']}', '$logAction')";
        $logResult = mysqli_query($conn, $logSql);

        if (!$logResult) {
            // Log the error or handle it appropriately
            error_log("Error inserting log entry: " . mysqli_error($conn));
        }
    }

    // Close the statement
    $checkJobTitleStmt->close();
}

// Fetch all job titles for the dropdown
$allJobTitlesSql = "SELECT job_title_id, job_title_name FROM job_titles";
$allJobTitlesResult = $conn->query($allJobTitlesSql);

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Job Title - LOGICALIS SA</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation Bar -->
<?php include 'navbar.php'; ?>

<!-- Main Content -->
<div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card option-card" style="text-align: center;">
          <h2>REMOVE JOB TITLE</h2>
          <hr>
          <?php
          // Display success alert if update was successful
          if ($success) {
            echo '<div class="alert alert-success" role="alert">';
            echo 'Job Title removed successfully!';
            echo '</div>';
          }

          // Display validation errors, if any
          if (!empty($errors)) {
            echo '<div class="alert alert-danger" role="alert">';
              foreach ($errors as $error) {
                echo $error . '<br>';
              }
            echo '</div>';
            echo '<script>alert("Validation errors occurred. Please check your entries.");</script>';
          }
          ?>
    
          <form id="deleteForm" method="post" action="a-remove-jobtitle.php">
            <div class="form-group">
              <label for="selectJobTitle">Select Job Title</label>
              <select class="form-control" id="selectJobTitle" name="selectJobTitle" required>
                <?php
                // Display all job titles in the dropdown
                while ($jobTitleRow = $allJobTitlesResult->fetch_assoc()) {
                    echo '<option value="' . $jobTitleRow['job_title_id'] . '">' . $jobTitleRow['job_title_name'] . '</option>';
                }
                ?>
              </select>
            </div>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">Remove Job Title</button>
          </form>
        </div>
      </div>
    </div>
  </div>    

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
function confirmDelete() {
    if (confirm("Are you sure you want to delete this job title?")) {
        document.getElementById("deleteForm").submit();
    }
}
</script>

</body>
</html>