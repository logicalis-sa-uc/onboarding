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
    $mainOfficeId = mysqli_real_escape_string($conn, $_POST['selectOffice']);
    $mainDepartmentId = mysqli_real_escape_string($conn, $_POST['selectDepartment']);
    $mainJobTitleId = mysqli_real_escape_string($conn, $_POST['selectJobTitle']);
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
    $departmentDetails = getDepartmentDetails($mainDepartmentId);

    // Extract manager name from the department details
    $Manager = $departmentDetails['manager_name'];
    $MyDepartment = $departmentDetails['department_name'];

    // Fetch office details based on office_id
    $officeDetails = getOfficeDetails($mainOfficeId);

    // Use the fetched office details
    $mainOffice = $officeDetails['office_name'];
    $MyStreetAddress = $officeDetails['street_address'];
    $MyCity = $officeDetails['city'];
    $MyState = $officeDetails['state'];
    $MyPostalCode = $officeDetails['postal_code'];
    $MyCountry = $officeDetails['country'];

    // Fetch job title details based on job_title_id
    $jobTitleDetails = getJobTitleDetails($mainJobTitleId);

    // Extract job title from the details
    $MyJobTitle = $jobTitleDetails['job_title_name'];

    // Insert the data into the database
    $query = "INSERT INTO advalues (EmployeeNumber, FirstName, LastName, UserPassword, Office, StreetAddress, City, State, PostalCode, Country, Department, Manager, JobTitle, MobileNumber, ExtensionNumber, SAMAccountName, UPN, FullName, EmailAddress, Website, CompanyName)
              VALUES ('$employeeNumber', '$firstName', '$lastName', '$password', '$mainOffice', '$MyStreetAddress', '$MyCity', '$MyState', '$MyPostalCode', '$MyCountry', '$MyDepartment', '$Manager', '$MyJobTitle', '$mobileNumber' , '$extensionNumber', '$samAccountName' ,'$upn', '$fullName', '$emailAddress', '$website', '$companyName')";

    // Execute the query (you should use prepared statements to prevent SQL injection)
    $result = $conn->query($query);

    if ($result) {
        // LDAP Operations
        $ldapAttributes = [
            'objectClass' => ['top', 'person', 'organizationalPerson', 'user'],
            'samaccountname' => $samAccountName,
            'c' => $MyCountry,
            'cn' => $fullName,
            'company' => $companyName,
            'department' => $MyDepartment,
            'displayName' => $fullName,
            'employeeNumber' => $employeeNumber,
            'givenName' => $firstName,
            'l' => $MyCity,
            'mail' => $emailAddress,
            'mobile' => $mobileNumber,
            'sn' => $lastName,
            'st' => $MyState,
            'streetAddress' => $MyStreetAddress,
            'postalcode' => $MyPostalCode,
            'title' => $MyJobTitle,
            'userPrincipalName' => $upn,
            'wwwhomepage' => $website,
            'ipphone' => $extensionNumber,
        ];

        $ldapDN = "CN=$fullName,OU=Users,OU=Automation,DC=automation,DC=ad";
        $ldapEntry = ldap_add($ldapConn, $ldapDN, $ldapAttributes);

        // Log the action using a regular mysqli_query
        $logAction = 'Added user: ' . $fullName . ' with Employee Number: ' . $employeeNumber;
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

        if ($ldapEntry) {
            echo "User added to Active Directory successfully!";
            header("Location: a-add-user.php");
            exit;

        } else {
            echo "Error adding user to Active Directory: " . ldap_error($ldapConn);
        }
    } else {
        echo "Error: " . $conn->error;
    }
?>
