<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('../src/statsLib.php');
require_once('template.php');

verifyLogin('statistics.php');

$errorMessages = '';

//Template header()
skinHeader();

/**
*
*
*
*/

function getNumUsers() {
	debug('Query number of users <br />');
	
	$db = connectToDB();
	
	$query = "SELECT * FROM `user` WHERE `username` LIKE '%'";
	mysql_query($query);
	echo "Number of tool users: ", mysql_affected_rows(), "\n";
	
	debug($query . '<br/>');
	
	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	debug('complete <br/>');
}

function getNumApproved() {
	debug('Query number of users <br />');

	$db = connectToDB();

	$query = "SELECT *  FROM `user` WHERE `approved` = 1";
	mysql_query($query);
	echo "Number of tool users approved: ", mysql_affected_rows(), "\n";

	debug($query . '<br/>');

	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}

	debug('complete <br/>');
}

function getNumActive() {
	debug('Query number of users <br />');

	$db = connectToDB();

	$query = "SELECT *  FROM `user` WHERE `active` = 1";
	mysql_query($query);
	echo "Number of tool users active: ", mysql_affected_rows(), "\n";

	debug($query . '<br/>');

	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}

	debug('complete <br/>');
}

function getNumAdmins() {
	debug('Query number of users <br />');

	$db = connectToDB();

	$query = "SELECT *  FROM `user` WHERE `toolAdmin` = 1";
	mysql_query($query);
	echo "Number of tool administrators: ", mysql_affected_rows(), "\n";

	debug($query . '<br/>');

	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}

	debug('complete <br/>');
}

function getNumCU() {
	debug('Query number of users <br />');

	$db = connectToDB();

	$query = "SELECT *  FROM `user` WHERE `checkuser` = 1";
	mysql_query($query);
	echo "Number of checkusers: ", mysql_affected_rows(), "\n";

	debug($query . '<br/>');

	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}

	debug('complete <br/>');
}

function getNumDevs() {
	debug('Query number of users <br />');

	$db = connectToDB();

	$query = "SELECT *  FROM `user` WHERE `developer` = 1";
	mysql_query($query);
	echo "Number of tool developers: ", mysql_affected_rows(), "\n";

	debug($query . '<br/>');

	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}

	debug('complete <br/>');
}

function getNumEmailTemplates() {
	debug('Query number of users <br />');

	$db = connectToDB();

	$query = "SELECT *  FROM `user` WHERE `developer` = 1";
	mysql_query($query);
	echo "Number of email templates: ", mysql_affected_rows(), "\n";

	debug($query . '<br/>');

	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}

	debug('complete <br/>');
}

function getNumUAs() {
	if(verifyAccess($GLOBALS['DEVELOPER'])){
		debug('Query number of users <br />');
	
		$db = connectToDB();
	
		$query = "SELECT *  FROM `cuData`";
		mysql_query($query);
		
		echo "Number of Useragents in DB: ", mysql_affected_rows(), "\n";
		
		debug($query . '<br/>');
	
		$result = mysql_query($query, $db);
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
	
		debug('complete <br/>');
	}
}

getNumUsers();
getNumApproved();
getNumActive();
getNumAdmins();
getNumCU();
getNumDevs();
getNumEmailTemplates();
getNumUAs();

skinFooter();

?>