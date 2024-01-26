<?php
// Start the session
session_start();

// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION['uName']) || empty($_SESSION['uName'])) {
    header("Location: onboarding-login.php");
    exit();
}
// Include the database configuration
include 'config.php';
include 'functions.php';
include 'ldap-config.php';

// Get departments from the database
$departments = getDepartmentData(); // You need to implement this function
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search User - LOGICALIS SA</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation Bar -->
<?php include 'a-nav-user.php'; ?>

<!-- Main Content -->
<div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card option-card" style="text-align: center;">
          <h2>SEARCH USER</h2>
          <hr>
          <form action="process-search-user.php" method="post">
            <div class="form-group">
              <label for="employeeNumber">Employee Number</label>
              <input type="text" class="form-control" id="employeeNumber" name="employeeNumber">
            </div>
            <div class="form-group">
              <label for="firstName">First Name</label>
              <input type="text" class="form-control" id="firstName" name="firstName">
            </div>
            <div class="form-group">
              <label for="departmentselect">Department</label>
              <select class="form-control" id="departmentselect" name="departmentselect">
                <option value=''></option>
                  <!-- Populate options from MySQL database -->
                  <?php
                    $departments = getDepartmentData(); // Implement this function to retrieve departments from MySQL
                      foreach ($departments as $department) {
                        echo "<option value='{$department['department_id']}'>{$department['department_name']}</option>";
                      }
                  ?>
              </select>
            </div>
            <button type="submit" class="btn btn-danger">Search</button>
          </form>
        </div>
      </div>
    </div>

</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Font Awesome (for icons) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

</body>
</html>