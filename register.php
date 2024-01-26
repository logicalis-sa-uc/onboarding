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
$uName = $pWord = $fullName = $contactNumber = $emailAddress = $accessLevel = $createdDate = '';

// Initialize an array to store validation errors
$errors = array();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user input
    $uName = trim($_POST['uName']);
    $pWord = password_hash($_POST['pWord'], PASSWORD_DEFAULT); // Hash the password for security
    $fullName = trim($_POST['fullName']);
    $contactNumber = trim($_POST['contactNumber']);
    $emailAddress = trim($_POST['emailAddress']);
    $accessLevel = $_POST['accessLevel'];
    $createdDate = date('Y-m-d H:i:s'); // Use the current date and time

    // Simple validation for demonstration purposes
    if (empty($uName)) {
        $errors[] = 'Username is required.';
    }
    if (empty($pWord)) {
        $errors[] = 'Password is required.';
    }
    // Add additional validation as needed

    // Check if the username or email address already exists
    $checkQuery = "SELECT * FROM lsa_users WHERE uName = '$uName' OR emailAddress = '$emailAddress'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        $errors[] = 'Username or email address already exists.';
    }

    // If there are no validation errors, insert data into the database
    if (empty($errors)) {
        $sql = "INSERT INTO lsa_users (uName, pWord, fullName, contactNumber, emailAddress, accessLevel, createdDate)
                VALUES ('$uName', '$pWord', '$fullName', '$contactNumber', '$emailAddress', '$accessLevel', '$createdDate')";

        if ($conn->query($sql) === TRUE) {
            // User registered successfully, redirect to onboarding-login.php
            header("Location: onboarding-login.php");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register User - LOGICALIS SA</title>
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
          <h5 class="card-title">REGISTER USER</h5>
          <hr>
          <!-- Display validation errors, if any -->
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul>
                <?php foreach ($errors as $error): ?>
                  <li><?php echo $error; ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <!-- User registration form -->
          <form method="post">
            <div class="form-group">
              <label for="uName">Username</label>
              <input type="text" class="form-control" id="uName" name="uName" value="<?php echo htmlspecialchars($uName); ?>">
            </div>
            <div class="form-group">
              <label for="pWord">Password</label>
              <input type="password" class="form-control" id="pWord" name="pWord">
            </div>
            <div class="form-group">
              <label for="fullName">Full Name</label>
              <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo htmlspecialchars($fullName); ?>">
            </div>
            <div class="form-group">
              <label for="contactNumber">Contact Number</label>
              <input type="text" class="form-control" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($contactNumber); ?>">
            </div>
            <div class="form-group">
              <label for="emailAddress">Email Address</label>
              <input type="email" class="form-control" id="emailAddress" name="emailAddress" value="<?php echo htmlspecialchars($emailAddress); ?>">
            </div>
            <div class="form-group">
              <label for="accessLevel">Access Level</label>
              <select class="form-control" id="accessLevel" name="accessLevel">
                <option value="administrator">Administrator</option>
                <option value="hr">HR</option>
                <option value="manager">Manager</option>
                <option value="supportdesk">Support Desk</option>
              </select>
            </div>
            <button type="submit" class="btn btn-danger">Register</button>
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