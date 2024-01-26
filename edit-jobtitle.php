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
$jobTitleId = $jobTitleName = '';

// Initialize an array to store validation errors
$errors = array();
$successMessage = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user input
    $jobTitleId = isset($_POST['selectJobTitle']) ? $_POST['selectJobTitle'] : '';
    $jobTitleName = trim($_POST['jobTitleName']);

    // Simple validation for demonstration purposes
    if (empty($jobTitleName)) {
        $errors[] = 'Job Title Name is required.';
    }

    // If there are no validation errors, update data in the database
    if (empty($errors)) {
        // Update the 'job_titles' table
        $updateJobTitleSql = "UPDATE job_titles SET job_title_name = ? WHERE job_title_id = ?";
        $updateJobTitleStmt = $conn->prepare($updateJobTitleSql);
        $updateJobTitleStmt->bind_param('si', $jobTitleName, $jobTitleId);
        $updateJobTitleStmt->execute();

        // Close the statement
        $updateJobTitleStmt->close();

        // Set success message
        $successMessage = 'Job Title updated successfully!';

        // Log the action
        $logAction = 'Updated job title with ID: ' . $jobTitleId;
        $logSql = "INSERT INTO lsa_logs (uName, logAction) VALUES ('{$_SESSION['uName']}', '$logAction')";

        if (mysqli_query($conn, $logSql)) {
            // Log entry inserted successfully
        } else {
            // Log entry failed to insert, handle the error if needed
            echo 'Error inserting log entry: ' . mysqli_error($conn);
        }
    }
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
    <title>Edit Job Title - LOGICALIS SA</title>
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
        <h2>EDIT JOB TTITLE</h2>
        <hr>
        <?php
          // Display validation errors, if any
          if (!empty($errors)) {
            echo '<div class="alert alert-danger" role="alert">';
            foreach ($errors as $error) {
              echo $error . '<br>';
            }
            echo '</div>';
            echo '<script>alert("Validation errors occurred. Please check your entries.");</script>';
          }

          // Display success message if available
          if (!empty($successMessage)) {
            echo '<div class="alert alert-success" role="alert">';
            echo $successMessage;
            echo '</div>';
          }
        ?>
    
        <form method="post" action="a-edit-jobtitle.php">
          <div class="form-group">
            <label for="selectJobTitle">Select Job Title</label>
            <select class="form-control" id="selectJobTitle" name="selectJobTitle">
                <?php
                // Display all job titles in the dropdown
                while ($jobTitleRow = $allJobTitlesResult->fetch_assoc()) {
                    $selected = ($jobTitleRow['job_title_id'] == $jobTitleId) ? 'selected' : '';
                    echo '<option value="' . $jobTitleRow['job_title_id'] . '" ' . $selected . '>' . $jobTitleRow['job_title_name'] . '</option>';
                }
                ?>
            </select>
          </div>
          <div class="form-group">
            <label for="jobTitleName">New Job Title Name</label>
            <input type="text" class="form-control" id="jobTitleName" name="jobTitleName" value="<?php echo $jobTitleName; ?>" required>
          </div>
          <button type="submit" class="btn btn-danger">Update Job Title</button>
          <a href="a-list-jobtitle.php" class="btn btn-secondary">Go Back</a>
        </form>
      </div>
    </div>      
  </div>
</div>  

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>