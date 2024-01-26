<?php

function getDepartmentDetails($mainDepartmentId) {
    global $conn;  // Make sure $conn is available in this context

    $result = $conn->query("SELECT * FROM departments WHERE department_id = $mainDepartmentId");

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $departmentInfo = $result->fetch_assoc();

    $result->free_result();

    return $departmentInfo;
}

// Add this function to functions.php
function getOfficeDetails($mainOfficeId) {
    global $conn;

    $result = $conn->query("SELECT * FROM offices WHERE office_id = $mainOfficeId");

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $officeDetails = $result->fetch_assoc();

    $result->free_result();

    return $officeDetails;
}

// Get Job Title Name
function getJobTitleDetails($mainJobTitleId) {
    global $conn;

    $result = $conn->query("SELECT * FROM job_titles WHERE job_title_id = $mainJobTitleId");

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $jobTitleDetails = $result->fetch_assoc();

    $result->free_result();

    return $jobTitleDetails;
}

// Function to fetch office data from the database
function getOfficeData() {
    global $conn;

    $officeData = array();

    $result = $conn->query("SELECT * FROM offices");

    if (!$result) {
        // Handle the error (for example, print it to see what went wrong)
        echo "Query failed";
        die("Query failed: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $officeData[] = $row;
    }

    $result->free_result();

    return $officeData;
}

// Get office data
$officeData = getOfficeData();

// Function to fetch department data from the database
function getDepartmentData() {
    global $conn;

    $departmentData = array();

    $result = $conn->query("SELECT * FROM departments");

    if (!$result) {
        // Handle the error (for example, print it to see what went wrong)
        die("Query failed: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $departmentData[] = $row;
    }

    $result->free_result();

    return $departmentData;
}

// Get department data
$departmentData = getDepartmentData();

// Function to fetch department data from the database
function getJobTitleData() {
    global $conn;

    $jobTitleData = array();

    $result = $conn->query("SELECT * FROM job_titles");

    if (!$result) {
        // Handle the error (for example, print it to see what went wrong)
        die("Query failed: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $jobTitleData[] = $row;
    }

    $result->free_result();

    return $jobTitleData;
}

// Get Job Title data
$jobTitleData = getJobTitleData();

// Function to get department name based on department ID
function getDepartmentName($departmentId) {
    global $departmentData;

    foreach ($departmentData as $department) {
        if ($department['department_id'] == $departmentId) {
            return $department['department_name'];
        }
    }

    return 'Unknown Department'; // Default value if department is not found
}

function getOfficeName($officeId) {
    global $officeData;

    foreach ($officeData as $office) {
        if ($office['office_id'] == $officeId) {
            return $office['office_name'];
        }
    }

    return 'Unknown Office'; // Default value if office is not found
}

// Function to get a user's job title name based on job title ID
function getJobTitleName($jobTitleId) {
    global $jobTitleData;

    foreach ($jobTitleData as $jobTitle) {
        if ($jobTitle['job_title_id'] == $jobTitleId) {
            return $jobTitle['job_title_name'];
        }
    }

    return 'Unknown Job Title'; // Default value if job title is not found
}

?>