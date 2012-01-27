<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');
require_once('../src/userObject.php');
require_once('../src/templateObj.php');
require_once('../src/appealObject.php');
require_once('template.php');

$errors = '';

if(!isset($_GET['id'])){
	// don't really know how to handle this, so...
	header("Location: " . getRootURL() . "home.php");
	// ...off you go
}

$id = $_GET['id'];

verifyLogin("appeal.php?id=" . $id);

skinHeader();

$appeal = Appeal::getAppealByID($id);
$admin = getCurrentUser();
echo "test1";
// confirm you have permission to email
if (!isset($appeal->getHandlingAdmin()) ||
$appeal->getHandlingAdmin() == null ) {
	displayError("<b>Access denied:</b> You must hold the reservation on appeal number " . $id . " to send an email to that user.");
} else{
	echo "test2";
	
}

skinFooter();

?>