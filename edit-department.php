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
$departmentId = $department = $manager = '';

// Initialize an array to store validation errors
$errors = array();

// Initialize a variable to track success
$success = false;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user input
    $selectedDepartmentId = $_POST['selectDepartment'];
    $department = trim($_POST['department']);
    $manager = trim($_POST['manager']);

    // Simple validation for demonstration purposes
    if (empty($selectedDepartmentId)) {
        $errors[] = 'Please select a department.';
    }

    // If there are no validation errors, update data in the database
    if (empty($errors)) {
        // Update the 'departments' table based on user input
        $updateDepartmentSql = "UPDATE departments SET ";
        $updateValues = array();

        if (!empty($department)) {
            $updateDepartmentSql .= "department_name = ?, ";
            $updateValues[] = $department;
        }

        if (!empty($manager)) {
            $updateDepartmentSql .= "manager_name = ?, ";
            $updateValues[] = $manager;
        }

        // Remove the trailing comma and space
        $updateDepartmentSql = rtrim($updateDepartmentSql, ', ');

        // Add the WHERE clause
        $updateDepartmentSql .= " WHERE department_id = ?";
        $updateValues[] = $selectedDepartmentId;

        // Prepare and bind parameters
        $updateDepartmentStmt = $conn->prepare($updateDepartmentSql);
        $types = str_repeat('s', count($updateValues)); // 'ss' for two strings
        $updateDepartmentStmt->bind_param($types, ...$updateValues);
        
        // Execute the statement
        $success = $updateDepartmentStmt->execute();

        // Log the action
        $logAction = 'Updated department with ID: ' . $selectedDepartmentId . ' to ' . $department . ' and Manager: ' . $manager;
        $logSql = "INSERT INTO lsa_logs (uName, logAction) VALUES (?, ?)";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param('ss', $_SESSION['uName'], $logAction);
        $logStmt->execute();

        // Close the log statement
        $logStmt->close();

        // Close the statement
        $updateDepartmentStmt->close();
    }
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
    <title>Edit Department - LOGICALIS SA</title>
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
          <h2>EDIT DEPARTMENT</h2>
          <hr>
          <?php
          // Display success alert if update was successful
          if ($success) {
            echo '<div class="alert alert-success" role="alert">';
            echo 'Update successful!';
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
          
          <form method="post" action="a-edit-department.php?id=<?php echo $departmentId; ?>">
            <div class="form-group">
              <label for="selectDepartment">Select Department</label>
              <select class="form-control" id="selectDepartment" name="selectDepartment">
                  <?php
                  // Display all departments in the dropdown
                  while ($deptRow = $allDepartmentsResult->fetch_assoc()) {
                    $selected = ($deptRow['department_id'] == $departmentId) ? 'selected' : '';
                    echo '<option value="' . $deptRow['department_id'] . '" ' . $selected . '>' . $deptRow['department_name'] . '</option>';
                  }
                  ?>
              </select>
            </div>
            <div class="form-group">
              <label for="department">New Department Name</label>
              <input type="text" class="form-control" id="department" name="department" value="<?php echo $department; ?>">
            </div>
            <div class="form-group">
              <label for="manager">New Manager Name</label>
              <input type="text" class="form-control" id="manager" name="manager" value="<?php echo $manager; ?>">
            </div>
            <button type="submit" class="btn btn-danger">Update Department</button>
            <a href="a-department-list.php" class="btn btn-secondary">Go Back</a>
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
