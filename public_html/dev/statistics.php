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

echo "<h2>Statistics:</h2><br>";

/**
*
*
*
*/

function getDBstatement($table, $field, $wildcard, $phrase) {
	debug('Query number of ' .$phrase.'<br />');
	
	$db = connectToDB();
	
	if (isset($field) && $wildcard) {$query = "SELECT * FROM `".$table."` WHERE `".$field."` LIKE '%'";}
	else if (isset($field) && !$wildcard) {	$query = "SELECT * FROM `".$table."` WHERE `".$field."` = 1";}
	else {throw new UTRSDatabaseException("Error in configuration of SQL command, no field is set to query from the table '".$table);}
	if (!isset($query)) {throw new UTRSDatabaseException("No SQL Query set.");}
	
	mysql_query($query);
	echo $phrase, mysql_affected_rows(), "<br>";
	
	debug($query . '<br/>');
	
	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	debug('complete <br/>');
}
getDBstatement("user", "username", True, "Number of tool users: ");
/*function getNumUsers() {
	debug('Query number of users <br />');
	
	$db = connectToDB();
	
	$query = "SELECT * FROM `user` WHERE `username` LIKE '%'";
	mysql_query($query);
	echo "Number of tool users: ", mysql_affected_rows(), "<br>";
	
	debug($query . '<br/>');
	
	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	debug('complete <br/>');
}*/

function getNumApproved() {
	debug('Query number of users <br />');

	$db = connectToDB();

	$query = "SELECT *  FROM `user` WHERE `approved` = 1";
	mysql_query($query);
	echo "Number of tool users approved: ", mysql_affected_rows(), "<br>";

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
	echo "Number of tool users active: ", mysql_affected_rows(), "<br>";

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
	echo "Number of tool administrators: ", mysql_affected_rows(), "<br>";

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
	echo "Number of checkusers: ", mysql_affected_rows(), "<br>";

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
	echo "Number of tool developers: ", mysql_affected_rows(), "<br>";

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

	$query = "SELECT *  FROM `template`";
	mysql_query($query);
	echo "Number of email templates: ", mysql_affected_rows(), "<br>";

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
		
		echo "Number of Useragents in DB: ", mysql_affected_rows(), "<br>";
		
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

//getNumUsers();
getNumApproved();
getNumActive();
getNumAdmins();
getNumCU();
getNumDevs();
getNumEmailTemplates();
getNumUAs();

skinFooter();

?>