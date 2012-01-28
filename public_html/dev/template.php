<?php
require_once('../src/unblocklib.php');

function skinHeader($script = '') {

$loggedIn = loggedIn();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
<link rel="stylesheet" href="unblock_styles.css">
<title>Unblock Ticket Request System - Register an Account</title>
<?php if($script){
	echo "<script type=\"text/javascript\">" . $script . "</script>";
}
?>
</head>
<body>
<div id="header"><a <?php if($loggedIn) { ?>href="home.php"<?php }else{ ?>href="index.php"<?php } ?> >
English Wikipedia<br />
Unblock Ticket Request System
</a></div>
<div id="subheader">
<table class="subheader_content">
<tr>
<?php if ($loggedIn) { ?>
	<td id="home" onClick="document.location.href='<?php echo getRootURL() . 'home.php'; ?>';">
		Home
	</td>
	<td id="stats" onClick="document.location.href='<?php echo getRootURL() . 'statistics.php'; ?>';">
		Statistics
	</td>
	<td id="mgmtTemp" onClick="document.location.href='<?php echo getRootURL() . 'tempMgmt.php'; ?>';">
		Manage/View Templates
	</td>
	<?php if(verifyAccess($GLOBALS['ADMIN'])) { ?>
	<td id="mgmtUser" onClick="document.location.href='<?php echo getRootURL() . 'userMgmt.php'; ?>';">
		Manage Users
	</td>
	<?php } ?>
	<td id="preferences" onClick="document.location.href='<?php echo getRootURL() . 'prefs.php'; ?>';">
		Preferences
	</td>
	<td id="privacyPolicy" onClick="document.location.href='<?php echo getRootURL() . 'privacy.php'; ?>';">
		Privacy Policy
	</td>
	<?php if(verifyAccess($GLOBALS['DEVELOPER'])) { ?>
	<td id="massEmail" onClick="document.location.href='<?php echo getRootURL() . 'massEmail.php'; ?>';">
		Send Mass Email
	</td>
	<?php } ?>
	<td id="logout" onClick="document.location.href='<?php echo getRootURL() . 'logout.php'; ?>';">
		Logout
	</td>
<?php } ELSE { ?>
	<td id="appealForm" onClick="document.location.href='<?php echo getRootURL() . 'index.php'; ?>';">
		Appeal a Block
	</td>
	<td id="GAB" onClick="document.location.href='http://en.wikipedia.org/wiki/Wikipedia:Guide_to_appealing_blocks';">
		Guide to Appealing Blocks
	</td>
	<td id="loginLink" onClick="document.location.href='<?php echo getRootURL() . 'login.php'; ?>';">
		Admins: Log in to review requests
	</td>
	<td id="register" onClick="document.location.href='<?php echo getRootURL() . 'register.php'; ?>';">
		Admins: Request an account
	</td>
	<td id="privacyPolicy" onClick="document.location.href='<?php echo getRootURL() . 'privacy.php'; ?>';">
		Privacy Policy
	</td>
<?php } ?>
</tr>
</table>
</div>
<div id="main">
<?php
}

function skinFooter() {
?>
<br />

</div>
<div id="footer">
The Unblock Ticket Request System is a project hosted on the Wikimedia Toolserver intended to assist
users with the unblock process on the English Wikipedia. <br />
This project is licensed under the
<a id="GPL" href="http://www.gnu.org/copyleft/gpl.html">GNU General Public License Version 3 or Later.</a><br />
For questions or assistance with the Unblock Ticket Request System, please email our development team at
<a href="mailto:unblock@toolserver.org">unblock AT toolserver DOT org</a><br />
</div>
</body>
</html>
<?php
}
?>