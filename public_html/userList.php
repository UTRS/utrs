<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/exceptions.php');
require_once('src/userObject.php');
require_once('src/statsLib.php');
require_once('template.php');

$errors = '';

skinHeader();

echo "<h2>Tool Users</h2>";

?>

<table style="background:none; border:none; width:80%;" cellspacing="0" cellpadding="0">
<tr>
<td style="width:50%" valign="top">
<?php 
if(verifyAccess($GLOBALS['ADMIN'])) {?>
<h3>Unapproved accounts</h3>

<?php echo printUnapprovedAccounts(); } ?>

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

<h3>WMF Staff</h3>

<?php echo printWMFAccounts(); ?>

<h3>Oversighters</h3>

<?php echo printOversighterAccounts(); ?>

<h3>Inactive accounts</h3>

<?php echo printInactiveAccounts(); ?>

<?php 
if(verifyAccess($GLOBALS['OVERSIGHT'])) {?>
<h3>Oversighted accounts</h3>

<?php 
	echo printOversightedAccounts();
}
 ?>

</td>
</tr>
</table>

<?php

skinFooter();

?>
