<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/languageCookie.php');
echo checkCookie();
$lang=getCookie();
require_once('src/exceptions.php');
require_once('src/userObject.php');
require_once('src/statsLib.php');
require_once('template.php');
require_once('src/messages.php');

$errors = '';

skinHeader();

echo "<h2>Tool Users</h2>";

?>

<table style="background:none; border:none; width:80%;" cellspacing="0" cellpadding="0">
<tr>
<td style="width:50%" valign="top">
<?php

$access=FALSE; 

if (verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['DEVELOPER'])) {
	$access=TRUE;
?>

<h3>Unapproved accounts</h3>

<?php echo printUnapprovedAccounts($access);

}

?>

<h3>Active accounts</h3>

<?php echo printActiveAccounts($access); ?>

<h3>Developers</h3>

<?php echo printDevelopers($access); ?>

</td>
<td style="width:50%" valign="top">
<h3>Tool administrators</h3>

<?php echo printAdmins($access); ?>

<h3>Checkusers</h3>

<?php echo printCheckusers($access); ?>

<h3>WMF Staff</h3>

<?php echo printWMFAccounts($access); ?>

<h3>Oversighters</h3>

<?php echo printOversighterAccounts($access); ?>

<h3>Inactive accounts</h3>

<?php echo printInactiveAccounts($access); ?>

<?php 
if(verifyAccess($GLOBALS['OVERSIGHT'])) {
?>
<h3>Oversighted accounts</h3>

<?php 
	echo printOversightedAccounts($access);
}
 ?>

</td>
</tr>
</table>

<?php

skinFooter();

?>
