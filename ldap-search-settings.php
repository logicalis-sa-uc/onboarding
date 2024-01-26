<?php
// LDAP Search settings
$ldap_usr_dom = "@automation.ad";
$ldap_dn = "OU=Users,OU=Automation,DC=automation,DC=ad";
$ldapFilter = "(objectClass=user)";
$ldapAttributes = ["samaccountname", "cn", "givenname", "sn", "mail", "mobile", "department", "useraccountcontrol", "l", "title", "employeenumber"];
?>