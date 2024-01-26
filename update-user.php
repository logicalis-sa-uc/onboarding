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

// Function to get the user's DN based on the username (samaccountname)
function getUserDN($samaccount) {
    global $ldapConn, $ldap_dn;
    $ldap_usr_dom = "@automation.ad";
    $ldap_dn = "OU=Users,OU=Automation,DC=automation,DC=ad";
    // $ldapFilter = "(objectClass=user)";
    $ldapAttributes = ["samaccountname", "cn", "givenname", "sn", "mail", "mobile", "department", "useraccountcontrol", "l", "title"];

    $ldapFilter = "(&(objectClass=user)(samaccountname=$samaccount))";
    $result = ldap_search($ldapConn, $ldap_dn, $ldapFilter);

    if ($result === false) {
        // Error in search query
        die("Error in search query: " . ldap_error($ldapConn));
    }

    $entries = ldap_get_entries($ldapConn, $result);

    if ($entries === false) {
        // Error retrieving entries
        die("Error getting entries: " . ldap_error($ldapConn));
    }

    if ($entries['count'] > 0) {
        return $entries[0]['dn'];
    } else {
        die("User $samaccount not found in Active Directory.");
    }
}

// Assuming the submitted form method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted form data
    $samaccount = $_POST['samaccount'] ?? null;
    $employeeNumber = $_POST['employeeNumber'] ?? null;
    $lastName = $_POST['lastName'] ?? null;
    $newPassword = $_POST['newPassword'] ?? null;
    $newOffice = $_POST['newOffice'] ?? null;
    $newDepartment = $_POST['newDepartment'] ?? null;
    $newJobTitle = $_POST['newJobTitle'] ?? null;
    $newMobileNumber = $_POST['newMobileNumber'] ?? null;
    $newExtensionNumber = $_POST['newExtensionNumber'] ?? null;

    // Get the user's DN from LDAP based on the submitted username (samaccountname)
    $username = $_POST['username'] ?? null;
    $userDN = getUserDN($samaccount); // Implement the getUserDN function in your ldap-config.php

    // Update the user attributes in Active Directory
    if ($userDN) {
        // Update employeeNumber
        if (!empty($employeeNumber)) {
            ldap_modify($ldapConn, $userDN, ['employeeNumber' => $employeeNumber]);
        }

        // Update lastName
        if (!empty($lastName)) {
            ldap_modify($ldapConn, $userDN, ['sn' => $lastName]);
        }

        // Update newPassword
        if (!empty($newPassword)) {
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            ldap_modify($ldapConn, $userDN, ['unicodePwd' => $newPasswordHash]);
        }

        // Update newOffice
        if (!empty($newOffice)) {
            ldap_modify($ldapConn, $userDN, ['officeAttribute' => $newOffice]);
        }

        // Update newDepartment
        if (!empty($newDepartment)) {
            ldap_modify($ldapConn, $userDN, ['department' => $newDepartment]);
        }

        // Update newJobTitle
        if (!empty($newJobTitle)) {
            ldap_modify($ldapConn, $userDN, ['title' => $newJobTitle]);
        }

        // Update newMobileNumber
        if (!empty($newMobileNumber)) {
            ldap_modify($ldapConn, $userDN, ['mobile' => $newMobileNumber]);
        }

        // Update newExtensionNumber
        if (!empty($newExtensionNumber)) {
            ldap_modify($ldapConn, $userDN, ['extensionAttribute' => $newExtensionNumber]);
        }

        echo "User information updated successfully.";
        header("Location: a-edit-user.php");
        exit();
    } else {
        echo "User not found in Active Directory.";
    }
} else {
    echo "Invalid request method.";
}
?>
