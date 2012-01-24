<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/userObject.php');
require_once('../src/statsLib.php');
require_once('template.php');

verifyLogin('userMgmt.php');

skinHeader();

if(!verifyAccess($GLOBALS['ADMIN'])){
	displayError("<b>Access denied:</a> User management is only available to tool administrators. "
	    . "Please click on one of the links above to return to another page.");
}
else{
	
	if(isset($_GET['userId'])){
		displayError("This hasn't been implemented yet. Sorry!");
	}
	else{
?>

<h2>User management</h2>

<table style="background:none; border:none; width:100%;" cellspacing="0" cellpadding="0">
<tr>
<td style="width:50%" valign="top">
<h3>Unapproved accounts</h3>

<?php echo printUnapprovedAccounts(); ?>

<h3>Active accounts</h3>

<?php echo printActiveAccounts(); ?>

<h3>Developers</h3>

<?php echo printDevelopers(); ?>

</td>
<td style="width:50%" valign="top">
<h3>Tool administrators</h3>

<?php echo printAdmins(); ?>

<h3>Checkusers</h3>

<?php echo printCheckusers(); ?>

<h3>Inactive accounts</h3>

<?php echo printInactiveAccounts(); ?>

</td>
</tr>
</table>

<?php 
	} // ends the else block from if(isset($_GET['userId']))

} // ends the else block from if(!verifyAccess($GLOBALS['ADMIN']))

skinFooter();

?>