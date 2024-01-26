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
$departmentId = $jobTitle = '';

// Initialize an array to store validation errors
$errors = array();

// Initialize variables for success message and actions
$successMessage = '';
$addAnother = false;

// Fetch existing departments for dropdown
$departments = array();
$fetchDepartmentsSql = "SELECT department_id, department_name FROM departments";
$fetchDepartmentsResult = $conn->query($fetchDepartmentsSql);

while ($row = $fetchDepartmentsResult->fetch_assoc()) {
    $departments[$row['department_id']] = $row['department_name'];
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user input
    $departmentId = $_POST['departmentId'];
    $jobTitle = trim($_POST['jobTitle']);

    // Simple validation for demonstration purposes
    if (empty($departmentId) || empty($jobTitle)) {
        $errors[] = 'All fields are required.';
    }

    // Check if the job title already exists in the selected department
    $checkSql = "SELECT * FROM job_titles WHERE department_id = ? AND job_title_name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('is', $departmentId, $jobTitle);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $errors[] = 'Job title already exists in the selected department.';
    }

    // If there are no validation errors, insert data into the database
    if (empty($errors)) {
        // Insert into the 'job_titles' table
        $insertJobTitleSql = "INSERT INTO job_titles (department_id, job_title_name) VALUES (?, ?)";
        $insertJobTitleStmt = $conn->prepare($insertJobTitleSql);
        $insertJobTitleStmt->bind_param('is', $departmentId, $jobTitle);
        $insertJobTitleStmt->execute();

        // Set success message and actions
        $successMessage = 'Job title added successfully!';
        $addAnother = true;

        // Log the action using a regular mysqli_query
        $logAction = 'Added Job Title: ' . $jobTitle;
        $logSql = "INSERT INTO lsa_logs (uName, logAction) VALUES ('{$_SESSION['uName']}', '$logAction')";

        if (mysqli_query($conn, $logSql)) {
          // Log entry inserted successfully  
        } else {
          // Log entry failed to insert, handle the error if needed
          echo 'Error inserting log entry: ' . mysqli_error($conn);
        }

        // Close the statement
        $insertJobTitleStmt->close();
    }

    // Close the check statement
    $checkStmt->close();
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Job Title - LOGICALIS SA</title>
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
          <h2>ADD JOB TITLE</h2>
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

            // Display success message and options
            if (!empty($successMessage)) {
              echo '<div class="alert alert-success" role="alert">' . $successMessage . '</div>';
              echo '<script>
                if (confirm("Job title added successfully! Do you want to add another?")) {
                    window.location.href = "a-add-jobtitle.php";
                } else {
                    window.location.href = "onboarding-admin.php";
                }
              </script>';
              }
            ?>
      
            <form method="post" action="a-add-jobtitle.php">
              <div class="form-group">
                <label for="departmentId">Department</label>
                <select class="form-control" id="departmentId" name="departmentId" required>
                  <option value="">Select Department</option>
                  <?php
                  foreach ($departments as $id => $name) {
                  echo '<option value="' . $id . '">' . $name . '</option>';
                  }
                  ?>
                </select>
              </div>
              <div class="form-group">
                <label for="jobTitle">Job Title</label>
                <input type="text" class="form-control" id="jobTitle" name="jobTitle" required>
              </div>
              <button type="submit" class="btn btn-danger">Add Job Title</button>
              <a href="onboarding-admin.php" class="btn btn-secondary">Go Back</a>
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