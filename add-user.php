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
// Includes
include 'config.php';
include 'functions.php';
include 'ldap-config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize user input
    $employeeNumber = mysqli_real_escape_string($conn, $_POST['employeeNumber']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $mainOffice = mysqli_real_escape_string($conn, $_POST['selectOffice']);
    $mainDepartment = mysqli_real_escape_string($conn, $_POST['selectDepartment']);
    $mainJobTitle = mysqli_real_escape_string($conn, $_POST['selectJobTitle']);
    $mobileNumber = mysqli_real_escape_string($conn, $_POST['mobileNumber']);
    $extensionNumber = mysqli_real_escape_string($conn, $_POST['extensionNumber']);

    // Other Variables
    $samAccountName = strtolower(str_replace(' ', '', $firstName)) . '.' . strtolower(str_replace(' ', '', $lastName));
    $upn = $samAccountName . "@automation.ad";
    $fullName = $firstName . " " . $lastName;
    $emailAddress = $samAccountName . "@za.logicalis.com";
    $website = "www.logicalis.com";
    $companyName = "Logicalis SA";

    // Validate user input
    $employeeNumber = filter_var($employeeNumber, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $firstName = filter_var($firstName, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $lastName = filter_var($lastName, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $password = filter_var($password, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $mobileNumber = filter_var($mobileNumber, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $extensionNumber = filter_var($extensionNumber, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

    // Get department details including manager
    $departmentDetails = getDepartmentDetails($mainDepartment);

    // Extract manager name from the department details
    $Manager = $departmentDetails['manager_name'];

    // Insert the data into the database
    $query = "INSERT INTO advalues (EmployeeNumber, FirstName, LastName, UserPassword, Office, StreetAddress, City, State, PostalCode, Country, Department, Manager, JobTitle, MobileNumber, ExtensionNumber, SAMAccountName, UPN, FullName, EmailAddress, Website, CompanyName)
              VALUES ('$employeeNumber', '$firstName', '$lastName', '$password', '$mainOffice', '$streetAddress', '$city', '$state', '$postalCode', '$country', '$mainDepartment', '$Manager', '$mainJobTitle', '$mobileNumber' , '$extensionNumber', '$samAccountName' ,'$upn', '$fullName', '$emailAddress', '$website', '$companyName')";

    // Execute the query (you should use prepared statements to prevent SQL injection)
    $result = $conn->query($query);

    if ($result) {
        // LDAP Operations
        $ldapAttributes = [
        'c' => $country,
        'cn' => $fullName,
        'company' => $companyName,
        'department' => $department,
        'displayname' => $fullName,
        'employeenumber' => $employeeNumber,
        'givenname' => $firstName,
        'l' => $city,
        'mail' => $emailAddress,
        'mobile' => $mobileNumber,
        'name' => $fullName,
        'physicaldeliveryofficename' => $city,
        'postalcode' => $postalCode,
        'samaccountname' => $samAccountName,
        'sn' => $lastName,
        'st' => $state,
        'streetaddress' => $streetAddress,
        'title' => $mainJobTitle,
        'userprincipalname' => $upn,
        'wwwhomepage' => $website
        ];

        $ldapDN = "CN=$fullName,OU=Users,OU=Automation,DC=automation,DC=ad";
        $ldapEntry = ldap_add($ldapConn, $ldapDn, $ldapAttributes);

        if ($ldapEntry) {
          echo "User added to Active Directory successfully!";
        } else {
          echo "Error adding user to Active Directory: " . ldap_error($ldapConn);
        }
    } else {
        echo "Error: " . $conn->error;
    }

    // Close the database connection
    mysqli_close($conn);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - LOGICALIS SA</title>
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
          <h2>User Input Form</h2>
          <hr>
          <!-- Form to get user input -->
          <form method="post" action="process-user-input.php">
            <div class="form-group">
              <label for="employeeNumber">Employee Number</label>
              <input type="text" class="form-control" id="employeeNumber" name="employeeNumber" required>
            </div>

            <div class="form-group">
              <label for="firstName">First Name</label>
              <input type="text" class="form-control" id="firstName" name="firstName" required>
            </div>

            <div class="form-group">
              <label for="lastName">Last Name</label>
              <input type="text" class="form-control" id="lastName" name="lastName" required>
            </div>

            <!-- <div class="form-group">
              <label for="password">Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div> -->

            <div class="form-group">
              <label for="selectOffice">Office</label>
              <select class="form-control" id="selectOffice" name="selectOffice" required>
                <?php
                  // Populate the Office dropdown dynamically
                  foreach ($officeData as $office) {
                      echo '<option value="' . $office['office_id'] . '">' . $office['office_name'] . '</option>';
                  }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label for="selectDepartment">Department</label>
              <select class="form-control" id="selectDepartment" name="selectDepartment" required>
                <?php
                  // Populate the Department dropdown dynamically
                  foreach ($departmentData as $department) {
                      echo '<option value="' . $department['department_id'] . '">' . $department['department_name'] . '</option>';
                  }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label for="selectJobTitle">Job Title</label>
              <select class="form-control" id="selectJobTitle" name="selectJobTitle" required>
                <?php
                // Populate the JobTitle dropdown dynamically with department names
                foreach ($jobTitleData as $jobTitle) {
                  $fullJobTitle = $jobTitle['job_title_name'] . ' -> ' . getDepartmentName($jobTitle['department_id']);
                  echo '<option value="' . $jobTitle['job_title_id'] . '">' . $fullJobTitle . '</option>';
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label for="mobileNumber">Mobile Number</label>
              <input type="text" class="form-control" id="mobileNumber" name="mobileNumber" required>
            </div>

            <div class="form-group">
              <label for="extensionNumber">Extension Number</label>
              <input type="text" class="form-control" id="extensionNumber" name="extensionNumber" required>
            </div>

            <button type="submit" class="btn btn-danger">Submit</button>
          </form>
        </div>
      </div>
  </div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


</body>
</html>