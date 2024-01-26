<?php
// Check if the session is not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
  
if (!isset($_SESSION['uName']) || empty($_SESSION['uName'])) {
    header("Location: onboarding-login.php");
    exit();
}

// Check the user's access level
$accessLevel = $_SESSION['accessLevel'];

// Define an array of navigation items and their corresponding links
$navItems = array(
    'Dashboard' => 'onboarding-dashboard.php',
    'Users' => array(
        'Add User' => 'add-user.php',
        'Edit User' => 'edit-user.php',
        'Search User' => 'search-user.php',
        'List Users' => 'list-user.php',
        'Disable User' => 'disable-user.php',
    ),
    'Department Setup' => array(
        'Add Department' => 'add-department.php',
        'Edit Department' => 'edit-department.php',
        'List Departments' => 'list-department.php',
        'Remove Department' => 'remove-department.php',
    ),
    'Job Titles Setup' => array(
        'Add Job Title' => 'add-jobtitle.php',
        'Edit Job Title' => 'edit-jobtitle.php',
        'List Job Titles' => 'list-jobtitle.php',
        'Remove Job Title' => 'remove-jobtitle.php',
    ),
);

// Add the 'Logs' item only for administrators & HR
if ($accessLevel === 'administrator' || $accessLevel === 'hr') {
    $navItems['Register User'] = 'register.php';
}

// Add the 'Logs' item only for administrators & HR
if ($accessLevel === 'administrator' || $accessLevel === 'hr') {
    $navItems['Logs'] = 'logs.php';
}

?>

<div class="logo-nav">
    <img src="./logicalis-logo-white_1.png">
</div>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <?php
            foreach ($navItems as $navItem => $navLink) {
                if (is_array($navLink)) {
                    // If the item is a dropdown
                    echo '<li class="nav-item dropdown">';
                    echo '<a class="nav-link dropdown-toggle" href="#" id="' . strtolower(str_replace(' ', '', $navItem)) . 'Dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    echo $navItem;
                    echo '</a>';
                    echo '<div class="dropdown-menu" aria-labelledby="' . strtolower(str_replace(' ', '', $navItem)) . 'Dropdown">';
                    foreach ($navLink as $subNavItem => $subNavLink) {
                        // Check if the user has access to this navigation item
                        $showNavItem = true;
                        if ($accessLevel === 'sd' || $accessLevel === 'manager') {
                            // Exclude specific items for hr, sd, and managers
                            $excludeItems = array('Register User', 'Add User', 'Disable User', 'Add Department', 'Remove Department', 'Add Job Title', 'Remove Job Title');
                            if (in_array($subNavItem, $excludeItems)) {
                                $showNavItem = false;
                            }
                        }

                        // Display the sub-navigation item
                        if ($showNavItem) {
                            echo '<a class="dropdown-item ' . ($_SERVER['PHP_SELF'] == "/onboarding/" . $subNavLink ? "active" : "") . '" href="' . $subNavLink . '">' . $subNavItem . '</a>';
                        }
                    }
                    echo '</div>';
                    echo '</li>';
                } else {
                    // If the item is not a dropdown
                    echo '<li class="nav-item ' . ($_SERVER['PHP_SELF'] == "/onboarding/" . $navLink ? "active" : "") . '">';
                    echo '<a class="nav-link" href="' . $navLink . '">' . $navItem . '</a>';
                    echo '</li>';
                }
            }
            ?>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>
