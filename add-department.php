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
$department = $manager = '';

// Initialize an array to store validation errors
$errors = array();

// Initialize variables for success message and actions
$successMessage = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user input
    $department = trim($_POST['department']);
    $manager = trim($_POST['manager']);

    // Simple validation for demonstration purposes
    if (empty($department) || empty($manager)) {
        $errors[] = 'All fields are required.';
    }

    // Check if the department already exists in the database
    $checkSql = "SELECT * FROM departments WHERE department_name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('s', $department);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $errors[] = 'Department already exists.';
    }

    // If there are no validation errors, insert data into the database
    if (empty($errors)) {
        // Insert into the 'departments' table
        $insertDepartmentSql = "INSERT INTO departments (department_name, manager_name) VALUES (?, ?)";
        $insertDepartmentStmt = $conn->prepare($insertDepartmentSql);
        $insertDepartmentStmt->bind_param('ss', $department, $manager);
        $insertDepartmentStmt->execute();

        // Set success message
        $successMessage = 'Department added successfully!';

         // Log the action using a regular mysqli_query
        $logAction = 'Added department: ' . $department . ' and manager: ' . $manager;
        $logSql = "INSERT INTO lsa_logs (uName, logAction) VALUES ('{$_SESSION['uName']}', '$logAction')";

        if (mysqli_query($conn, $logSql)) {
          // Log entry inserted successfully  
        } else {
          // Log entry failed to insert, handle the error if needed
          echo 'Error inserting log entry: ' . mysqli_error($conn);
        }

        // Close the statement
        $insertDepartmentStmt->close();
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
  <title>Add Department - LOGICALIS SA</title>
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
          <h2>ADD DEPARTMENT</h2>
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

            // Display success message
            if (!empty($successMessage)) {
              echo '<div class="alert alert-success" role="alert">' . $successMessage . '</div>';
            }
            ?>
            <form method="post" action="a-add-department.php">
              <div class="form-group">
                <label for="department">Department</label>
                <input type="text" class="form-control" id="department" name="department" required>
              </div>
              <div class="form-group">
                <label for="manager">Manager</label>
                <input type="text" class="form-control" id="manager" name="manager" required>
              </div>
              <button type="submit" class="btn btn-danger">Add Department</button>
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