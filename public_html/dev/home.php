<?php
session_start();

//For development purposes.  Obviously remove once the login system is developed
$_SESSION['loggedin'] = true;

//Check that the user is logged in before displaying page
if ($_SESSION['loggedin'] == TRUE) {


//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('recaptchalib.php');
require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('../src/statsLib.php');

$publickey = '6Le92MkSAAAAANADTBB8wdC433EHXGpuP_v1OaOO';
$privatekey = '6Le92MkSAAAAAH1tkp8sTZj_lxjNyBX7jARdUlZd';
$errorMessages = '';
$appeal = null;
$email = null;
$blocker = null;
$appealText = null;
$edits = null;
$otherInfo = null;

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
<a id="privacyPolicy" href="privacy.html">Privacy Policy</a>
</td>
</tr>
</table>
</div>
<div id="main">

<h2>New Requests</h2>
<?php echo printNewRequests(); ?>

<h2>Flagged Requests</h2>
<?php echo printFlaggedRequests(); ?>

<h2>Checkuser Needed</h2>
<?php echo printCheckuserNeeded(); ?>

<h2>Last 5 closed requests</h2>
<?php echo printRecentClosed(); ?>

</div>
</body>
</html>
<?php 

//This bracket closes the logged in conditional
}

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