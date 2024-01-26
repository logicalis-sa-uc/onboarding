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
$departmentId = '';

// Initialize an array to store validation errors
$errors = array();

// Initialize a variable to track success
$success = false;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user input
    $departmentId = isset($_POST['selectDepartment']) ? $_POST['selectDepartment'] : '';

    // Check if the department exists in the database
    $checkDepartmentSql = "SELECT * FROM departments WHERE department_id = ?";
    $checkDepartmentStmt = $conn->prepare($checkDepartmentSql);
    $checkDepartmentStmt->bind_param('i', $departmentId);
    $checkDepartmentStmt->execute();
    $checkDepartmentResult = $checkDepartmentStmt->get_result();

    if ($checkDepartmentResult->num_rows === 0) {
        $errors[] = 'Department not found.';
    } else {
        // Remove the department from the 'departments' table
        $removeDepartmentSql = "DELETE FROM departments WHERE department_id = ?";
        $removeDepartmentStmt = $conn->prepare($removeDepartmentSql);
        $removeDepartmentStmt->bind_param('i', $departmentId);
        $removeDepartmentStmt->execute();
        $removeDepartmentStmt->close();

        // Remove any associated entries in the 'job_titles' table
        $removeJobTitlesSql = "DELETE FROM job_titles WHERE department_id = ?";
        $removeJobTitlesStmt = $conn->prepare($removeJobTitlesSql);
        $removeJobTitlesStmt->bind_param('i', $departmentId);
        $removeJobTitlesStmt->execute();
        $removeJobTitlesStmt->close();

        // Set success to true
        $success = true;

        // Insert log information
        $logAction = "Removed department with ID: $departmentId";
        $logSql = "INSERT INTO lsa_logs (uName, logAction) VALUES ('{$_SESSION['uName']}', '$logAction')";
        $logResult = mysqli_query($conn, $logSql);

        if (!$logResult) {
            // Log the error or handle it appropriately
            error_log("Error inserting log entry: " . mysqli_error($conn));
        }
    }

    // Close the statement
    $checkDepartmentStmt->close();
}

// Fetch all departments for the dropdown
$allDepartmentsSql = "SELECT department_id, department_name FROM departments";
$allDepartmentsResult = $conn->query($allDepartmentsSql);

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Department - LOGICALIS SA</title>
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
          <h2>REMOVE DEPARTMENT</h2>
          <hr>
          <?php
          // Display success alert if update was successful
          if ($success) {
            echo '<div class="alert alert-success" role="alert">';
            echo 'Department removed successfully!';
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
    
          <form id="deleteForm" method="post" action="a-remove-department.php">
            <div class="form-group">
              <label for="selectDepartment">Select Department</label>
              <select class="form-control" id="selectDepartment" name="selectDepartment" required>
                <?php
                // Display all departments in the dropdown
                while ($deptRow = $allDepartmentsResult->fetch_assoc()) {
                    echo '<option value="' . $deptRow['department_id'] . '">' . $deptRow['department_name'] . '</option>';
                }
                ?>
              </select>
            </div>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">Remove Department</button>
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
    if (confirm("Are you sure you want to delete this department?")) {
        document.getElementById("deleteForm").submit();
    }
}
</script>

</body>
</html>