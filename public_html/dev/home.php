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
<title>English Wikipedia Internal Unblock Request Interface</title>
<style type="text/css" media="screen">
	@import "http://toolserver.org/~acc/style.css";

</style>
</head>
<body>
<div id="header">
	<div id="header-title">
		English Wikipedia Internal Unblock Request Interface
	</div>
</div>
<div id="navigation">
	<a href="home.php">Home</a>
	<a href="requests.php">Unblock Requests</a>
	<a href="mgmt.php?page=template">Template Management</a>
	<a href="mgmt.php?page=ban">Ban Management</a>
	
	<a href="stats.php">Statistics</a>
	<a href="search.php">Search</a>
	<a href="mgmt.php?page=preg">Preferences</a>
	<a href="logout.php">Logout</a>
	
</div>
<div id="content">

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