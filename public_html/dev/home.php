<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('../src/statsLib.php');
require_once('template.php');

// make sure user is logged in, if not, kick them out
verifyLogin('home.php');

$errorMessages = '';

//Template header()
skinHeader();

echo '<p>Welcome, ' . $_SESSION['user'] . '.</p>';
?>

<table style="background:none; border:none; width:100%;" cellspacing="0" cellpadding="0">
<tr>
<td style="width:50%" valign="top">
<h2>New Requests</h2>
<?php echo printNewRequests(); ?>

<h2>Awaiting feedback from WP:OPP</h2>
<?php echo printProxyCheckNeeded(); ?>

<h2>Last 5 closed requests</h2>
<?php echo printRecentClosed(); ?>

</td>
<td style="width:50%" valign="top">

<h2>User replied - awaiting reviewer response</h2>
<?php echo printUserReplied();?>

<h2>Checkuser Needed</h2>
<?php echo printCheckuserNeeded(); ?>

<h2>Awaiting user response</h2>
<?php echo printUserReplyNeeded(); ?>
</td>
</tr>
</table>

<?php 

//Template footer()
skinFooter();

?>