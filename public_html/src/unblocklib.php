<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('exceptions.php');

function getRootURL(){
	return 'http://toolserver.org/~unblock/dev/';
}

/**
 * echo's a debugging string if $DEBUG_MODE is set to true
 * @param String $message the message to echo
 */
function debug($message){
	$DEBUG_MODE = false;
	if($DEBUG_MODE){
		echo $message;
	}
}

/**
 * Connects to and returns a resource link to the UTRS database
 * @param boolean suppressOutput - true to disable debug statements, should be used
 * 				only on redirection pages.
 * @throws UTRSDatabaseException
 */
function connectToDB($suppressOutput){
	if(!$suppressOutput){
		debug('connectToDB <br />');
	}
	$ts_pw = posix_getpwuid(posix_getuid());
	$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/.my.cnf");
	$db = mysql_connect("sql-s1-user.toolserver.org", $ts_mycnf['user'], $ts_mycnf['password'], true);
	if($db == false){
		debug(mysql_error());
		throw new UTRSDatabaseException("Failed to connect to database cluster sql-s1-user!");
	}
	mysql_select_db("p_unblock", $db);
	if(!$suppressOutput){
		debug('exiting connectToDB');
	}
	return $db;
}

/**
 * Returns a URL to the given page on the English Wikipedia.
 * @param String $page the page to link to, including namespace
 * @param boolean $useSecure true to use the secure server
 * @param String $queryOptions query options, such as used on some log pages,
 *  separated by (but not starting with) &'s.
 */
function getWikiLink($page, $useSecure, $queryOptions){
	$url = "http";
	if($useSecure){
		$url .= "s://secure.wikimedia.org/wikipedia/en/";
	}
	else{
		$url .= "://en.wikipedia.org/";
	}
	if($queryOptions){
		$url .= "w/index.php?title=" . $page . "&" . $queryOptions;
	}
	else{
		$url .= 'wiki/' . $page;
	}
	
	return $url;
}

?>