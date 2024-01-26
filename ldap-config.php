<?php

// LDAP Connection settings
$ldapServer = ""; // Change this to your Active Directory server
$ldapUsername = "";
$ldapPassword = "";

// Establish LDAP connection
$ldapConn = ldap_connect($ldapServer);
if (!$ldapConn) {
    die("LDAP connection failed");
}

// Bind to the LDAP server with the provided username and password
$ldapBind = ldap_bind($ldapConn, $ldapUsername, $ldapPassword);
if (!$ldapBind) {
    die("LDAP bind failed. Check the username and password.");
}

?>