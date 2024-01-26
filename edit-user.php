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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Users - LOGICALIS SA</title>
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
    <h2>EDIT USERS</h2>
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
                <!-- Add more columns as needed -->
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
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#editUserModal<?= $i ?>">
                            <i class="fas fa-pencil-alt"></i> <!-- Font Awesome pencil icon -->
                        </button>

                        <!-- Edit User Modal -->
                        <div class="modal fade" id="editUserModal<?= $i ?>" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="editUserModalLabel">Edit User - <?= $data[$i]['samaccountname'][0] ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <!-- Edit User Form -->
                                <form action="update-user.php" method="post">
                                <input class="form-control" id="samaccount" name="samaccount" type="hidden" value="<?= $data[$i]['samaccountname'][0] ?>">
                                  <div class="form-group">
                                    <label for="employeeNumber">New Employee Number</label>
                                    <input type="text" class="form-control" id="employeeNumber" name="employeeNumber" value="<?= isset($data[$i]['employeeNumber'][0]) ? $data[$i]['employeeNumber'][0] : '' ?>">
                                  </div>
                                  <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" value="<?= isset($data[$i]['lastName'][0]) ? $data[$i]['lastName'][0] : '' ?>">
                                  </div>
                                  <div class="form-group">
                                    <label for="newPassword">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" name="newPassword">
                                  </div>
                                  <div class="form-group">
                                    <label for="newOffice">New Office</label>
                                    <select class="form-control" id="newOffice" name="newOffice">
                                    <!-- Populate options from MySQL database -->
                                      <?php
                                        $offices = getOfficeData(); // Implement this function to retrieve offices from MySQL
                                        foreach ($offices as $office) {
                                        echo "<option value='{$office['office_name']}'>{$office['office_name']}</option>";
                                        }
                                      ?>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                    <label for="newDepartment">New Department</label>
                                    <select class="form-control" id="newDepartment" name="newDepartment">
                                    <!-- Populate options from MySQL database -->
                                      <?php
                                        $departments = getDepartmentData(); // Implement this function to retrieve departments from MySQL
                                        foreach ($departments as $department) {
                                          echo "<option value='{$department['department_name']}'>{$department['department_name']}</option>";
                                        }
                                      ?>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                    <label for="newJobTitle">New Job Title</label>
                                    <select class="form-control" id="newJobTitle" name="newJobTitle">
                                    <!-- Populate options from MySQL database -->
                                      <?php
                                        $jobTitles = getJobTitleData(); // Implement this function to retrieve job titles from MySQL
                                        foreach ($jobTitles as $jobTitle) {
                                          echo "<option value='{$jobTitle['job_title_name']}'>{$jobTitle['job_title_name']}</option>";
                                        }
                                      ?>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                    <label for="newMobileNumber">New Mobile Number</label>
                                    <input type="text" class="form-control" id="newMobileNumber" name="newMobileNumber" value="<?= isset($data[$i]['newMobileNumber'][0]) ? $data[$i]['newMobileNumber'][0] : '' ?>">
                                  </div>
                                  <div class="form-group">
                                    <label for="newExtensionNumber">New Extension Number</label>
                                    <input type="text" class="form-control" id="newExtensionNumber" name="newExtensionNumber" value="<?= isset($data[$i]['newExtensionNumber'][0]) ? $data[$i]['newExtensionNumber'][0] : '' ?>">
                                  </div>
                                  <button type="submit" class="btn btn-danger">Save Changes</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                    <!-- Add more columns as needed -->
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

<!-- Font Awesome (for pencil icon) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

</body>
</html>