<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

echo 'entering unblocklib <br/>';
require_once('exceptions.php');
echo 'import done <br/>';

$DEBUG_MODE = false;

function debug($message){
	if($DEBUG_MODE){
		echo $message;
	}
}

function connectToDB(){
	debug('connectToDB <br />');
	$ts_pw = posix_getpwuid(posix_getuid());
	$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/.my.cnf");
	$db = mysql_connect("sql-s1-user.toolserver.org", $ts_mycnf['user'], $ts_mycnf['password'], true);
	if($db == false){
		debug(mysql_error());
		throw new UTRSDatabaseException("Failed to connect to database cluster sql-s1-user!");
	}
	mysql_select_db("p_unblock", $db);
	debug('exiting connectToDB');
	return $db;
}

?>