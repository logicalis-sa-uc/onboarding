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

// Function to update address information based on office
function updateAddressInformation($conn, $newOffice) {
    // Fetch the office details based on the new office
    $officeQuery = "SELECT * FROM offices WHERE office_name = ?";
    $officeResult = queryWithParams($conn, $officeQuery, "s", $newOffice);

    if ($officeResult->num_rows > 0) {
        $officeData = $officeResult->fetch_assoc();

        // Update the user's address information
        $newStreetAddress = $officeData['street_address'];
        $newCity = $officeData['city'];
        $newState = $officeData['state'];
        $newPostalCode = $officeData['postal_code'];
        $newCountry = $officeData['country'];

        $updateAddressQuery = "UPDATE advalues SET
                                StreetAddress = ?,
                                City = ?,
                                State = ?,
                                PostalCode = ?,
                                Country = ?
                                WHERE Office = ?";

        queryWithParams($conn, $updateAddressQuery, "ssssss", $newStreetAddress, $newCity, $newState, $newPostalCode, $newCountry, $newOffice);
    } else {
        echo "Office details not found for office: $newOffice";
    }
}

// Function to update manager based on department
function updateManagerBasedOnDepartment($conn, $newDepartment) {
    // Fetch the department details based on the new department
    $departmentQuery = "SELECT * FROM departments WHERE department_name = ?";
    $departmentResult = queryWithParams($conn, $departmentQuery, "s", $newDepartment);

    if ($departmentResult->num_rows > 0) {
        $departmentData = $departmentResult->fetch_assoc();

        // Update the user's manager information
        $newManager = $departmentData['manager'];

        $updateManagerQuery = "UPDATE advalues SET
                               Manager = ?
                               WHERE Department = ?";

        queryWithParams($conn, $updateManagerQuery, "ss", $newManager, $newDepartment);
    } else {
        echo "Department details not found for department: $newDepartment";
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve values from the form
    $newEmployeeNumber = $_POST['newEmployeeNumber'];
    $newFullName = $_POST['newFullName'];
    $newLastName = $_POST['newLastName'];
    $newPassword = $_POST['newPassword'];
    $newOffice = $_POST['newOffice'];
    $newDepartment = $_POST['newDepartment'];
    $newJobTitle = $_POST['newJobTitle'];
    $newMobileNumber = $_POST['newMobileNumber'];
    $newExtensionNumber = $_POST['newExtensionNumber'];

    // Update user details in the database
    $updateUserQuery = "UPDATE advalues SET
                        FullName = ?,
                        LastName = ?,
                        Password = ?,
                        Office = ?,
                        Department = ?,
                        JobTitle = ?,
                        MobileNumber = ?,
                        ExtensionNumber = ?
                        WHERE EmployeeNumber = ?";

    queryWithParams($conn, $updateUserQuery, "ssssssssi", $newFullName, $newLastName, $newPassword, $newOffice, $newDepartment, $newJobTitle, $newMobileNumber, $newExtensionNumber, $newEmployeeNumber);

// Additional logic for updating address information and manager based on changes
updateAddressInformation($conn, $newOffice);
updateManagerBasedOnDepartment($conn, $newDepartment);

// Insert log information
$logAction = "Updated user: $newFullName with Employee Number: $newEmployeeNumber";
$logSql = "INSERT INTO lsa_logs (uName, logAction) VALUES ('{$_SESSION['uName']}', '$logAction')";
$logResult = mysqli_query($conn, $logSql);

if (!$logResult) {
    echo "Error inserting log entry: " . mysqli_error($conn);
}

// Redirect to the page where you list users
header("Location: a-list-user.php");
exit();
} else {
echo "Error updating user details: " . mysqli_error($conn);
}

// Redirect to the page where you list users
header("Location: a-list-user.php");
exit();

// Close the database connection
$conn->close();

// Function to execute a prepared statement with parameters
function queryWithParams($conn, $query, $types, ...$params) {
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    return $stmt->get_result();
}
?>
