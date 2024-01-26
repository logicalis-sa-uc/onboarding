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
include 'functions.php';
include 'ldap-config.php';
include 'ldap-search-settings.php';

// Perform LDAP search
$result = ldap_search($ldapConn, $ldap_dn, $ldapFilter, $ldapAttributes) or die("Error in search query: " . ldap_error($ldapConn));
$data = ldap_get_entries($ldapConn, $result);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userDN = $_POST['userDN'];

  // Confirm that the user wants to disable the account
  if (isset($_POST['confirmDisable']) && $_POST['confirmDisable'] == 'yes') {
      // Update the useraccountcontrol attribute to disable the user
      $disableResult = ldap_modify($ldapConn, $userDN, ['useraccountcontrol' => 546]);

      if ($disableResult) {

      // Log the action using a regular mysqli_query
      $logAction = 'Disabled user: ' . $userDN;
      $logSql = "INSERT INTO lsa_logs (uName, logAction) VALUES ('{$_SESSION['uName']}', '$logAction')";

      if (mysqli_query($conn, $logSql)) {
        // Log entry inserted successfully
      } else {
        // Log entry failed to insert, handle the error if needed
        echo 'Error inserting log entry: ' . mysqli_error($conn);
      }

      // Close the database connection
      mysqli_close($conn);
      } else {
        echo "Error: " . $conn->error;
      }

          echo "User account disabled successfully.";
      } else {
          echo "Failed to disable user account: " . ldap_error($ldapConn);
      }
  } else {
      echo "User account was not disabled.";
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disable Users - LOGICALIS SA</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation Bar -->
<?php include 'navbar.php'; ?>

  <!-- Main Content -->
  <div class="container mt-5" 
    style="background-color: white; border: solid black 2px; color: black; text-align: center; padding-top: 15px; border-radius: 20px;">
    <h2>Disable User</h2>
    <hr>
<!-- Display users from Active Directory in a table -->
<table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Employee Number</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Mobile Number</th>
                <th>Branch</th>
                <th>Department</th>
                <th>Job Title</th>
                <th>Account Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Loop through LDAP entries and display in the table -->
            <?php for ($i = 0; $i < $data['count']; $i++): ?>
                <tr>
                    <td><?= $data[$i]['employeenumber'][0] ?></td>
                    <td><?= $data[$i]['samaccountname'][0] ?></td>
                    <td><?= $data[$i]['cn'][0] ?></td>
                    <td><?= isset($data[$i]['mail'][0]) ? $data[$i]['mail'][0] : 'N/A' ?></td>
                    <td><?= isset($data[$i]['mobile'][0]) ? $data[$i]['mobile'][0] : 'N/A' ?></td>
                    <td><?= isset($data[$i]['l'][0]) ? $data[$i]['l'][0] : 'N/A' ?></td>
                    <td><?= isset($data[$i]['department'][0]) ? $data[$i]['department'][0] : 'N/A' ?></td>
                    <td><?= isset($data[$i]['title'][0]) ? $data[$i]['title'][0] : 'N/A' ?></td>
                    <td><?= getUserAccountStatus($data[$i]['useraccountcontrol'][0]) ?></td>
                    <td>
                    <!-- Button trigger modal -->
                      <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#disableUserModal<?= $i ?>">
                        <i class="fas fa-cancel"></i> <!-- Font Awesome cancel icon -->
                      </button>

                      <!-- Disable User Modal -->
                      <div class="modal fade" id="disableUserModal<?= $i ?>" tabindex="-1" role="dialog" aria-labelledby="disableUserModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="disableUserModalLabel">Disable User - <?= $data[$i]['samaccountname'][0] ?></h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <!-- Confirmation form -->
                              <form action="a-disable-user.php" method="post">
                                <input type="hidden" name="userDN" value="<?= $data[$i]['dn'] ?>">
                                <p>Are you sure you want to disable this user?</p>
                                <div class="form-group">
                                  <label for="confirmDisable">Type 'yes' to confirm:</label>
                                    <input type="text" class="form-control" id="confirmDisable" name="confirmDisable">
                                </div>
                                <button type="submit" class="btn btn-danger">Disable User</button>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <?php

    // Function to determine account status based on useraccountcontrol
    function getUserAccountStatus($userAccountControl) {
      if ($userAccountControl == 544) {
          return 'Enabled';
      } elseif ($userAccountControl == 546) {
          return 'Disabled';
      } else {
          return 'N/A';
      }
    }

    ?>

</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Font Awesome (for cancel icon) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

</body>
</html>