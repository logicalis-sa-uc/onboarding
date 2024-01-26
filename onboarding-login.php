<?php
include 'config.php';
session_start();

$uName = $pWord = '';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uName = trim($_POST['uName']);
    $pWord = trim($_POST['pWord']);

    if (empty($uName) || empty($pWord)) {
        $errors[] = 'Username or password cannot be empty.';
    }

    if (empty($errors)) {
        $sql = "SELECT * FROM lsa_users WHERE uName = ? OR emailAddress = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $uName, $uName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($pWord, $row['pWord'])) {
                $_SESSION['uName'] = $uName;
                $_SESSION['accessLevel'] = $row['accessLevel'];
                $_SESSION['fullName'] = $row['fullName'];

                header("Location: onboarding-dashboard.php");
                exit();
            } else {
                $errors[] = 'Incorrect password.';
            }
        } else {
            $errors[] = 'Invalid username or email address.';
        }

        $stmt->close();
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Onboarding Automation - LOGICALIS SA</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="style-form.css">
</head>
<body>

  <!-- Navigation Bar -->
  <div class="logo-nav">
    <img src="./logicalis-logo-white_1.png">
  </div>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item active">
          <a class="nav-link" href="index.php"><strong>Home</strong></a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Content -->
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card option-card" style="text-align: center;">
          <h2 class="card-title">MICROSOFT AD AUTOMATION</h2>
          <h4 class="card-text">LOGIN</h4>
          <hr>
          <!-- Add your login form here -->
          <form method="post" action="onboarding-login.php" onsubmit="return validateForm()">
            <div class="form-group">
              <label for="uName">USERNAME / EMAIL</label>
              <input type="text" class="form-control" id="uName" name="uName" placeholder="Enter your username">
            </div>
            <div class="form-group">
              <label for="pWord">PASSWORD</label>
              <input type="password" class="form-control" id="pWord" name="pWord" placeholder="Enter your password">
            </div>
            <button type="submit" class="btn btn-danger">LOGIN</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts-->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    function validateForm() {
      var uName = document.getElementById('uName').value;
      var pWord = document.getElementById('pWord').value;

      if (uName.trim() === '' || pWord.trim() === '') {
        alert('Username or password cannot be empty.');
        return false;
      }

      return true;
    }

    <?php
      if (!empty($errors)) {
        echo "alert('{$errors[0]}');";
      }
    ?>
  </script>
</body>
</html>