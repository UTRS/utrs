<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('recaptchalib.php');
require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('../src/statsLib.php');
require_once('template.php');

// make sure user is logged in, if not, kick them out
verifyLogin('home.php');

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
skinHeader();

echo '<p>Welcome, ' . $_SESSION['username'] . '.</p>';
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
skinFooter();

?>