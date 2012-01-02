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
require_once('template.php');

$publickey = '6Le92MkSAAAAANADTBB8wdC433EHXGpuP_v1OaOO';
$privatekey = '6Le92MkSAAAAAH1tkp8sTZj_lxjNyBX7jARdUlZd';
$errorMessages = '';
$appeal = null;
$email = null;
$blocker = null;
$appealText = null;
$edits = null;
$otherInfo = null;

//Template header()
header();
?>

<h2>New Requests</h2>
<?php echo printNewRequests(); ?>

<h2>Flagged Requests</h2>
<?php echo printFlaggedRequests(); ?>

<h2>Checkuser Needed</h2>
<?php echo printCheckuserNeeded(); ?>

<h2>Last 5 closed requests</h2>
<?php echo printRecentClosed(); ?>

<?php 

//Template footer()
footer();
//This bracket closes the logged in conditional
}

?>