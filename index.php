<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LOGICALIS SA</title>
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom styles -->
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Jumbotron -->
  <div class="jumbotron">
    <img src="./LSA-Logo-White.PNG">
    <hr>
    <br>
    <h2>Welcome to our platform for automation solutions.</h2>
  </div>

  <!-- Options -->
  <div class="container options-container">
    <div class="row">
      <?php
        // Function to generate option card
        function generateOptionCard($link, $title, $description) {
          echo '<div class="col-md-4">';
          echo '<a href="' . $link . '" class="text-decoration-none">';
          echo '<div class="card option-card">';
          echo '<h5 class="card-title">' . $title . '</h5>';
          echo '<p class="card-text">' . $description . '</p>';
          echo '</div>';
          echo '</a>';
          echo '</div>';
        }

        // Generate option cards
        generateOptionCard("onboarding-login.php", "Onboarding Automation", "The automated onboarding/offboarding process.");
        generateOptionCard("server-login.php", "Server Automation", "Streamlined server management with automation.");
        generateOptionCard("user-login.php", "User Automation", "Automated user-related tasks for increased efficiency.");
      ?>
    </div>
  </div>

  <!-- Bootstrap JS and Popper.js -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>