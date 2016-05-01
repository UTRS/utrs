<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('exceptions.php');
require_once('userObject.php');
require_once(dirname(__FILE__) . '/config.inc.php');

$GLOBALS['WMF'] = -3;
$GLOBALS['OVERSIGHT'] = -2;
$GLOBALS['CHECKUSER'] = -1;
$GLOBALS['APPROVED'] = 0;
$GLOBALS['ACTIVE'] = 1;
$GLOBALS['ADMIN'] = 2;
$GLOBALS['DEVELOPER'] = 3;

/**
 * Removes all <, >, and $ signs from a text string and replaces them with
 * HTML entities.
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

function posted($key) {
	if (isset($_POST[$key])) {
		return htmlspecialchars($_POST[$key]);
	}

	return '';
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

		$query = $db->prepare('
			SELECT userID FROM user
			WHERE username = :username
			  AND passwordHash = :passwordHash');

		$result = $query->execute(array(
			':username'	=> $user,
			':passwordHash'	=> $password));

		if($result === false){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}

		$data = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();

		if ($data !== false) {
			registerLogin($data['userID'], $db);
			return true;
		}
	}
	return false;
}

function registerLogin($userID, $db){
	$query = $db->prepare("
		INSERT INTO loggedInUsers (userID, lastPageView)
			VALUES (:userID, NOW())

		ON DUPLICATE KEY
			UPDATE lastPageView = NOW()");

	$result = $query->execute(array(
		':userID'	=> $userID));

	if(!$result){
		$error = var_export($query->errorInfo(), true);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
}

function getLoggedInUsers(){
	$db = connectToDB();
		
	// Clear old users: Trash collection
	$query = $db->exec("DELETE FROM loggedInUsers WHERE lastPageView < SUBTIME(NOW(), '0:5:0')");
	
	// should be within the last five minutes, I think
	$query = $db->query("SELECT userID FROM loggedInUsers");
	
	if($query === false){
		$error = var_export($db->errorInfo(), true);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	$users = array();
	
	while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
		$user = User::getUserById($data['userID']);
		$users[] = "<a href=\"userMgmt.php?userId=" . $user->getUserId() . "\">" . $user->getUsername() . "</a>";
	}
	
	return implode(', ', $users);
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
	} else {	
		if (!$user->getAcceptToS() && $_SERVER['REQUEST_URI'] != "/accepttos.php") {
			header("Location: " . getRootURL() . 'accepttos.php');
			exit;
		}
	}
}

/**
 * Confirm user is logged in AND has the necessary access level to proceed
 * @param $level int - the access level required:
 * VALID ARGUMENTS:
 * -3 - Only WMF Staff may view this
 * -2 - Only oversight may view this
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
			$level !== $GLOBALS['ADMIN'] & $level !== $GLOBALS['DEVELOPER'] & $level !== $GLOBALS['WMF'] & $level !== $GLOBALS['OVERSIGHT']){
		throw new UTRSIllegalArgumentException($level, '-3, -2, -1, 0, 1, 2, or 3', 'verifyAccess()');
	}
	
	$user = getCurrentUser();
	if($user == null){
		return false;
	}
	
	switch($level){
		case $GLOBALS['WMF']: return $user->isWMF(); 
		case $GLOBALS['OVERSIGHT']: return ($user->isOversighter() | $user->isWMF()); 
		case $GLOBALS['CHECKUSER']: return ($user->isCheckuser() | $user->isWMF()); // doesn't cascase up like others
		case $GLOBALS['APPROVED']: return $user->isApproved(); // will never be set back to zero, so don't need to check rest
		case $GLOBALS['ACTIVE']: return ($user->isActive()); // on second thought, it should be possible to disable admins and devs too
		case $GLOBALS['ADMIN']: return ($user->isAdmin() | $user->isDeveloper());
		case $GLOBALS['DEVELOPER']: return $user->isDeveloper();
	}
	//Add protection incase things fail
	return false;
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

	static $pdo = false;

	if(!$suppressOutput){
		debug('connectToDB <br />');
	}

	if ($pdo !== false) {
		return $pdo;
	}

	try {
		$pdo = new PDO($CONFIG['db']['dsn'], $CONFIG['db']['user'], $CONFIG['db']['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
	} catch (PDOException $pdo_ex) {
		debug($pdo_ex->getMessage());
		throw new UTRSDatabaseException("Failed to connect to database server!");
	}

	if(!$suppressOutput){
		debug('exiting connectToDB');
	}

	return $pdo;
}

/**
 * Returns a URL to the given page on the English Wikipedia.
 * @param String $page the page to link to, including namespace
 * @param boolean $useSecure true to use the secure server
 * @param array $queryOptions query options, such as used on some log pages,
 *  as an associative array.
 */
function getWikiLink($basepage, $useSecure = false, array $queryOptions = array()){
	// MediaWiki doesn't properly decode +, so turn spaces into _ before
	// urlencode gets a chance to turn them into +.
	$basepage = str_replace(' ', '_', $basepage);

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

function getVersion() {
	return exec("git describe --tags --dirty=-dev --abbrev=0");
}

?>
