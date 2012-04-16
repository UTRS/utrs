<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('exceptions.php');
require_once('userObject.php');
require_once('src/config.inc.php');

$GLOBALS['CHECKUSER'] = -1;
$GLOBALS['APPROVED'] = 0;
$GLOBALS['ACTIVE'] = 1;
$GLOBALS['ADMIN'] = 2;
$GLOBALS['DEVELOPER'] = 3;

/**
 * Removes all <, >, and $ signs from a text string and replaces them with
 * HTML entities. YOU STILL NEED TO SANITIZE FOR QUOTES USING mysql_real_escape_string
 * @param String $text
 * @return String $text
 */
function sanitizeText($text){
	$text = str_replace("<", "&lt;", $text);
	$text = str_replace(">", "&gt;", $text);
	$text = str_replace("$", "&#36;", $text);
	$text = str_replace("\"", "&quot;", $text);
	$text = str_replace("'", "&apos;", $text);
	return $text;
}

function loggedIn(){	
	if(!isset($_SESSION)){
		session_name('UTRSLogin');
		session_start();
	}
	
	if(isset($_SESSION['user']) && isset($_SESSION['passwordHash'])){
		// presumably good, but confirming that the cookie is valid...
		$user = $_SESSION['user'];
		$password = $_SESSION['passwordHash'];
		$db = connectToDB(true);
		$query = 'SELECT userID FROM user WHERE username=\'' . $user . '\' AND passwordHash=\'' . $password . '\'';
		$result = mysql_query($query, $db);
		if($result === false){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		if(mysql_num_rows($result) == 1){
			$data = mysql_fetch_assoc($result);
			registerLogin($data['userID'], $db);
			return true;
		}
		if(mysql_num_rows($result) > 1){
			throw new UTRSDatabaseException('There is more than one record for your username. '
			. 'Please contact a tool developer immediately.');
		}
	}
	return false;
}

function registerLogin($userID, $db){
	$query = "SELECT * FROM loggedInUsers WHERE userID='" . $userID . "'";
	debug($query);
	
	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	$rows = mysql_num_rows($result);
	
	if($rows){
		$query = "UPDATE loggedInUsers SET lastPageView=NOW() WHERE userID='" . $userID . "'";
	}
	else{
		$query = "INSERT INTO loggedInUsers (userID, lastPageView) VALUES ('" . $userID . "', NOW())";
	}
	debug($query);
	
	$result = mysql_query($query, $db);
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
}

function getLoggedInUsers(){
	$db = connectToDB();
	// Clear old users: Trash collection
	$query = "DELETE FROM loggedInUsers WHERE lastPageView < SUBTIME(NOW(), '0:5:0');";
	
	$result = mysql_query($query, $db);
	
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	// should be within the last give minutes, I think
	$query = "SELECT userID FROM loggedInUsers";
	
	debug($query);
	
	$result = mysql_query($query, $db);
	
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	$users = "";
	
	$rows = mysql_num_rows($result);
	
	for($i = 0; $i < $rows; $i++){
		$data = mysql_fetch_assoc($result);
		$user = User::getUserById($data['userID']);
		if($users){
			$users .= ", ";
		}
		$users .= $user->getUsername();
	}
	
	return $users;
}

/**
 * Confirm user is logged in; if not, kick them out to the login page.
 * @param string $destination the page to go to once logged in ('home.php', 'mgmt.php', etc.)
 */
function verifyLogin($destination = 'home.php'){
	if(!loggedIn()){
		header("Location: " . getRootURL() . 'login.php?destination=' . $destination);
		exit;
	}
	// if user has somehow lost access, kick them out
	$user = User::getUserByUsername($_SESSION['user']);
	if(!$user->isApproved() | !$user->isActive()){
		header("Location: " . getRootURL() . 'logout.php');
		exit;
	}
}

/**
 * Confirm user is logged in AND has the necessary access level to proceed
 * @param $level int - the access level required:
 * VALID ARGUMENTS:
 * -1 - Only checkusers may view this
 *  0 - Any approved user, including inactive ones, can view (or above)
 *  1 - Only active users may view this 
 *  2 - Only tool administrators may view this (or above)
 *  3 - Only tool developers may view this
 * Invalid arguments will result in an exception.
 * @return true if the user has at least the specified access level
 */
function verifyAccess($level){
	// validate
	if($level !== $GLOBALS['CHECKUSER'] & $level !== $GLOBALS['APPROVED'] & $level !== $GLOBALS['ACTIVE'] & 
			$level !== $GLOBALS['ADMIN'] & $level !== $GLOBALS['DEVELOPER']){
		throw new UTRSIllegalArgumentException($level, '-1, 0, 1, 2, or 3', 'verifyAccess()');
	}
	
	$user = getCurrentUser();
	if($user == null){
		return false;
	}
	
	switch($level){
		case $GLOBALS['CHECKUSER']: return $user->isCheckuser(); // doesn't cascase up like others
		case $GLOBALS['APPROVED']: return $user->isApproved(); // will never be set back to zero, so don't need to check rest
		case $GLOBALS['ACTIVE']: return ($user->isActive()); // on second thought, it should be possible to disable admins and devs too
		case $GLOBALS['ADMIN']: return ($user->isAdmin() | $user->isDeveloper());
		case $GLOBALS['DEVELOPER']: return $user->isDeveloper();
	}
}

/**
 * Returns http://toolserver.org/~unblock/p/ if on the live site or
 * http://toolserver.org/~unblock/beta/ if on the beta site
 */
function getRootURL(){
	global $CONFIG;

	return $CONFIG['site_root'];
}

/**
 * echo's a debugging string if $DEBUG_MODE is set to true
 * @param String $message the message to echo
 */
function debug($message){
	$DEBUG_MODE = FALSE;
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
function connectToDB($suppressOutput = false){
	global $CONFIG;

	if(!$suppressOutput){
		debug('connectToDB <br />');
	}

	$db = mysql_connect($CONFIG['db']['host'], $CONFIG['db']['user'], $CONFIG['db']['password'], true);
	if($db == false){
		debug(mysql_error());
		throw new UTRSDatabaseException("Failed to connect to database server " . $CONFIG['db']['host'] . "!");
	}

	mysql_select_db($CONFIG['db']['database'], $db);

	if(!$suppressOutput){
		debug('exiting connectToDB');
	}

	return $db;
}

/**
 * Returns a URL to the given page on the English Wikipedia.
 * @param String $page the page to link to, including namespace
 * @param boolean $useSecure true to use the secure server
 * @param array $queryOptions query options, such as used on some log pages,
 *  as an associative array.
 */
function getWikiLink($basepage, $useSecure = false, array $queryOptions = array()){
	//trigger_error("basepage: $basepage");
	//trigger_error("url encoded: " .urlencode($basepage));
	$prefix = $useSecure ? "https:" : "http:";
	$url = sprintf("%s//en.wikipedia.org/wiki/%s", $prefix, urlencode($basepage));
	$first = true;
	foreach($queryOptions as $key => $value){
		$savekey = urlencode($key);
		$savevalue = urlencode($value);
		$separator = $first ? '?' : '&';
		$url .= "$separator$savekey=$savevalue";
		$first = false;
	}
	return $url;
}

function getCurrentUser(){
	if(loggedIn()){
		return User::getUserByUsername($_SESSION['user']);
	}
	return null;
}

/**
 Validate an email address.
 Provide email address (raw input)
 Returns true if the email address has the email
 address format and the domain exists.

 This function taken from http://www.linuxjournal.com/article/9585?page=0,3
 as it's for linux and posted for anyone to use, I shall assume it's ok
 with licensing and such.
 */
function validEmail($email)
{
	// if does not contain an @ or @ is first character
	if(strpos($email, "@") === false || strpos($email, "@") === 0){
		return false;
	}
	// get the domain and user parts, assumed to be separated
	// at the last @ in the email addy
	$user = substr($email, 0, strrpos($email, "@"));
	$domain = substr($email, strrpos($email, "@") + 1);
	// validate user side
	$userArray = str_split($user, 1);
	$length = sizeof($userArray);
	// local part may only be 64 characters long
	if($length > 64){
		return false;
	}
	$inQuotes = false;
	$inComment = false;
	$escapeNext = false;
	// this is a somewhat slow way of doing things, but avoids potential
	// for errors.
	for($i = 0; $i < $length; $i++){
		$char = $userArray[$i];
		// normal stuff
		if(preg_match("/^[a-zA-Z0-9!#$%&'*+\-\/_=?^+`{}|~]$/", $char)){
			$escapeNext = false; // don't need to escape these, but don't see why you can't
			continue; // nothing to worry about
		}
		// special characters not including ., (, ), ", \
		if(preg_match("/^[ ,:;<>@\[\]]$/", $char)){
			if($escapeNext){
				$escapeNext = false;
				continue; // character properly escaped, move on
			}
			if($inQuotes || $inComment){
				continue; // nobody cares, move on
			}
			return false; // illegal character
		}
		// dot
		if(preg_match("/^[.]$/", $char)){
			if($inQuotes || $inComment){
				continue; // nobody cares, move on
			}
			// if first, last, or next character is also a dot
			if($i == 0 || $i == ($length - 1) || preg_match("/^[.]$/", $userArray[$i+1])){
				return false;
			}
			$escapeNext = false;
			continue; // otherwise we don't care
		}
		// quote
		if(preg_match("/^[\"]$/", $char)){
			if($inComment){
				echo "In comment\n";
				continue; // nobody cares, move on
			}
			if(!$inQuotes){
				// if first or previous character is a dot
				if($i == 0 || preg_match("/^[.]$/", $userArray[$i-1])){
					$inQuotes = true;
					continue; // start of valid quoted string, carry on
				}
			}
			else{
				// if last or next character is a dot
				if($i == ($length - 1) || preg_match("/^[.]$/", $userArray[$i+1])){
					$inQuotes = false;
					continue; // end of valid quoted string, carry on
				}
			}
			if($escapeNext){
				$escapeNext = false;
				continue; // escaped, carry on
			}
			return false; // otherwise invalid character
		}
		// backslash
		if(preg_match("/^[\\\\]$/", $char)){
			if($escapeNext){
				$escapeNext = false;
				continue; // escaped, carry on
			}
			// if last
			else if($i == ($length - 1)){
				return false; // can't be last, as that escapes the @, 
				              // making the address not actually have an @ 
			}
			else{
				$escapeNext = true;
				continue; // escape whoever's next, carry on
			}
		}
		// open paren
		if(preg_match("/^[(]$/", $char)){
			if($escapeNext){
				return false; // not a valid character by itself
			}
			$inComment = true;
			continue; // keep going
		}
		// close paren
		if(preg_match("/^[)]$/", $char)){
			if($inComment){
				if($escapeNext){
					$escapeNext = false;
					continue; // escaped, carry on
				}
				$inComment = false;
				continue;
			}
			return false; // otherwise invalid character
		}
		return false; // if not told to continue by now, the character is invalid
	}
	if($inQuotes || $inComment || $escapeNext){
		return false;
	}
	// end user validation
	// begin domain validation
	// remove comments
	$domain = preg_replace("/\(.*?\)/", "", $domain);
	// IP address wrapped in [] (e.g. [127.0.0.1])
	if(!(preg_match("/^\[((2[0-4][0-9]|25[0-5]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3,3}".
	   "(2[0-4][0-9]|25[0-5]|1[0-9][0-9]|[1-9][0-9]|[0-9])\]$/", $domain)) && 
		!(preg_match("/^[a-zA-Z0-9-]+?(\.[a-zA-Z0-9-]+?){0,}$/", $domain))){
		return false;
	}
	else if(strlen($domain) > 255){
		return false;
	}
	
	return true; // yay!
}

/**
 * Displays a pretty error box
 */
function displayError($errorMsg){
	echo "<table class=\"error\"><tr><td>" . $errorMsg . "</td></tr></table>";
}

/**
 * Displays a pretty success box
 */
function displaySuccess($successMsg){
	echo "<table class=\"success\"><tr><td>" . $successMsg . "</td></tr></table>";
}

function censorEmail($email){
	if(!validEmail($email)){
		return $email;
	}
	$domainStart = strpos($email, "@");
	$email = "******" . substr($email, $domainStart);
	return $email;
}

?>
