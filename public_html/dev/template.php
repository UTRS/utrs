<?php
require_once('../src/unblocklib.php');

function skinHeader() {

$loggedIn = loggedIn();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
<link rel="stylesheet" href="unblock_styles.css">
<title>Unblock Ticket Request System - Register an Account</title>

</head>
<body>
<div id="header">
English Wikipedia<br />
Unblock Ticket Request System
</div>
<div id="subheader">
<table class="subheader_content">
<tr>
<?php if ($loggedIn) { ?>
	<td>
		<a id="homePage" href="home.php">Home</a>
	</td>
	<td>
		<a id="stats" href="statistics.php">Statistics</a>
	</td>
	<td>
		<a id="mgmtTemp" href="mgmt.php?page=template">Manage Templates</a>
	</td>
	<td>
		<a id="mgmtUser" href="mgmt.php?page=user">Manage Users</a>
	</td>
	<td>
		<a id="preferences" href="prefs.php">Preferences</a>
	</td>
	<td>
		<a id="privacyPolicy" href="privacy.html">Privacy Policy</a>
	</td>
	<td>
		<a id="logout" href="logout.php">Logout</a>
	</td>
<?php } ELSE { ?>
	<td>
		<a id="appealForm" href="index.php">Appeal a Block</a>
	</td>
	<td>
		<a id="GAB" href="http://en.wikipedia.org/wiki/Wikipedia:Guide_to_appealing_blocks">Guide to Appealing Blocks</a>
	</td>
	<td>
		<a id="loginLink" href="login.php">Admins: Log in to review requests</a>
	</td>
	<td>
		<a id="register" href="register.php">Admins: Request an account</a>
	</td>
	<td>
		<a id="privacyPolicy" href="privacy.html">Privacy Policy</a>
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