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
// Include the necessary files and configurations
include 'config.php';
include 'functions.php';
include 'ldap-config.php';
include 'ldap-search-settings.php';

// Retrieve search criteria from the form
$employeeNumber = isset($_POST['employeeNumber']) ? $_POST['employeeNumber'] : '';
$firstName = isset($_POST['firstName']) ? $_POST['firstName'] : '';
$departmentselect = isset($_POST['departmentselect']) ? $_POST['departmentselect'] : '';

// Construct LDAP filter based on the provided criteria
$ldapFilter = "(&(objectClass=user)";
if (!empty($employeeNumber)) {
    $ldapFilter .= "(employeeNumber=$employeeNumber)";
}
if (!empty($firstName)) {
    $ldapFilter .= "(givenName=$firstName)";
}
if (!empty($department)) {
    $ldapFilter .= "(department=$departmentselect)";
}
$ldapFilter .= ")";

// Perform LDAP search
$result = ldap_search($ldapConn, $ldap_dn, $ldapFilter, $ldapAttributes) or die("Error in search query: " . ldap_error($ldapConn));
$data = ldap_get_entries($ldapConn, $result);

// Display the search results in a table
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
<?php include 'navbar.php'; ?>

<!-- Main Content -->
<div class="container mt-5" 
    style="background-color: white; border: solid black 2px; color: black; text-align: center; padding-top: 15px; border-radius: 20px;">
    <h2>SEARCH RESULTS</h2>
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

<!-- Font Awesome (for icons) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

</body>
</html>