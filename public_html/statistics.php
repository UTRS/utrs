<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('src/languageCookie.php');
echo checkCookie();
$lang=getCookie();
require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/appealObject.php');
require_once('src/statsLib.php');
require_once('template.php');
require_once('src/messages.php');

verifyLogin('statistics.php');

$errorMessages = '';


//Template header()
skinHeader();

echo "<h2>Statistics:</h2><br>";

function getDBstatement($table, $field, $wildcard, $phrase, $userlevel) {
	if(verifyAccess($GLOBALS[$userlevel])){
		debug('Query number of ' .$phrase.'<br />');
		
		$db = connectToDB();

		if (isset($field) && $wildcard) {
			$query = "SELECT COUNT(*) FROM `".$table."` WHERE `".$field."` LIKE '%'";
		}
		else if (isset($field) && !$wildcard) {
			$query = "SELECT COUNT(*) FROM `".$table."` WHERE `".$field."` = 1";
		}
		else if (!isset($field) && !$wildcard) {
			$query = "SELECT COUNT(*) FROM `".$table."`";
		}
		else {throw new UTRSDatabaseException("Error in configuration of SQL command with the table '".$table);
		}
		if (!isset($query)) {
			throw new UTRSDatabaseException("No SQL Query set.");
		}

		$query = $db->query($query);
		
		if($query === false){
			$error = var_export($db->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$count = $query->fetchColumn();
		$query->closeCursor();

		echo $phrase, $count, "<br>";
		
		debug('complete <br/>');
	}
	
}

function getTopAdmins() {
	$db = connectToDB();

	$query = "select count(*) as Total, username FROM user, appeal where userID = handlingAdmin Group by username ORDER BY Total DESC LIMIT 0,10;";
	
	$query = $db->query($query);
	
	echo "<h4>Thank you</h4>";
	
	while ($row = $query->fetch()) {
		echo $row["Total"] . " - " . $row["username"] . "<br>";
	}
	
	echo "<br>";
}

getDBstatement("user", "username", True, "Number of tool users: ", "APPROVED");
getDBstatement("user", "approved", False, "Number of tool users approved: ", "APPROVED");
getDBstatement("user", "active", False, "Number of tool users active: ", "APPROVED");
getDBstatement("user", "toolAdmin", False, "Number of tool administrators: ", "APPROVED");
getDBstatement("user", "checkuser", False, "Number of checkusers: ", "APPROVED");
getDBstatement("user", "developer", False, "Number of tool developers: ", "APPROVED");
getDBstatement("template", Null, False, "Number of email templates: ", "ACTIVE");
getDBstatement("cuData", Null, False, "Number of Useragents in DB: ", "DEVELOPER");

echo "<br>";

getTopAdmins();

echo "<h4>Last 30 Actions</h4>";
echo printLastThirtyActions();

skinFooter();

?>
