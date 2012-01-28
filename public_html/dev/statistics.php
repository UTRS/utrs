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
	
	mysql_query("SELECT * FROM `user` WHERE `username` LIKE '%'");
	echo "Number of tool users: %d\n", mysql_affected_rows();
	
	debug($query . '<br/>');
	
	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	debug('complete <br/>');
}

getNumUsers();

skinFooter();

?>