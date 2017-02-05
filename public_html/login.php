<?php
## THIS SECTION MUST NOT SEND ANY OUTPUT TO THE SCREEN. ##
##      DOING SO WILL CAUSE THE REDIRECTION TO FAIL.    ##
##      THIS INCLUDES ALL USE OF THE debug() METHOD.    ##

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/network.php');
forceHTTPS();
require_once('src/unblocklib.php');
require_once('src/oauth.php');
require_once('template.php');
require_once('src/exceptions.php');
require_once('sitemaintain.php');

checkOnline();
require_once('src/oauth.php');

if(loggedIn()){
	header("Location: " . getRootURL() . 'home.php');
	exit;
}

$user = '';
$destination = '';
$errors = '';
$logout = '';
if(isset($_GET['logout'])){
	$logout = true;
}
if(isset($_POST['destination'])){
	$destination = $_POST['destination'];
}
else if(isset($_GET['destination'])){
	$destination = $_GET['destination'];
}
else{
	$destination = getRootURL() . 'home.php';
}

$gConsumerKey=$CONFIG['oauth']['consumerKey'];
$gConsumerSecret=$CONFIG['oauth']['consumerSecret'];

 

debug('Destination: ' . $destination . '  Logout: ' . $logout . '</br>');


/* BEGIN OAUTH */

/**
 * Set this to the Special:OAuth/authorize URL. 
 * To work around MobileFrontend redirection, use /wiki/ rather than /w/index.php.
 */
$mwOAuthAuthorizeUrl = 'https://www.mediawiki.org/wiki/Special:OAuth/authorize';

/**
 * Set this to the Special:OAuth URL. 
 * Note that /wiki/Special:OAuth fails when checking the signature, while
 * index.php?title=Special:OAuth works fine.
 */

if (!isset($_GET['logout']) && isset($_GET['wiki'])) {
	$wiki = $_GET['wiki'];
}
else if (!isset($_GET['logout'])) {
	header("Location: " . getRootURL() . 'loginsplash.php');
	die();
}
if (!isset($_GET['logout'])) { 
	if (isset($wiki) && $_GET['wiki'] === "enwiki"){
		$mwOAuthUrl = 'https://en.wikipedia.org/w/index.php?title=Special:OAuth';
	}
	else if (isset($wiki) && $_GET['wiki'] === "meta"){
		$mwOAuthUrl = 'https://meta.wikimedia.org/w/index.php?title=Special:OAuth';
	}
	else {
		header("Location: " . getRootURL() . 'loginsplash.php');
		die();
		
	}
}
//Coded by DQ but never deployed. Was going to be used for #122, but I don't know the intended consequences and other fixes may work.
/*if (session_status() == PHP_SESSION_ACTIVE) {
	if (isset($_GET['id'])) {
		header("Location: " . getRootURL() . 'appeal.php?id='.$_GET['id']);
		die();
	}
	else {
		header("Location: " . getRootURL() . 'home.php');
		die();
	}
}*/
/**
 * Set this to the interwiki prefix for the OAuth central wiki.
 */
$mwOAuthIW = 'mw';

/**
 * Set this to the API endpoint
 */

if (isset($wiki) && $_GET['wiki'] === "enwiki"){
	$apiUrl = 'https://en.wikipedia.org/w/api.php';
}
else if (isset($wiki) && $_GET['wiki'] === "meta"){
	$apiUrl = 'https://meta.wikimedia.org/w/api.php';
}

/**
 * This should normally be "500". But Tool Labs insists on overriding valid 500
 * responses with a useless error page.
 */
$errorCode = 200;


// Setup the session cookie
session_name( 'utrs_oauth' );
$params = session_get_cookie_params();
session_set_cookie_params(
    $params['lifetime'],
    dirname( $_SERVER['SCRIPT_NAME'] )
);


$gUserAgent = @$CONFIG['oauth']['agent'];
$gConsumerKey = @$CONFIG['oauth']['consumerKey'];
$gConsumerSecret = @$CONFIG['oauth']['consumerSecret'];

// Load the user token (request or access) from the session
$gTokenKey = '';
$gTokenSecret = '';
if (isset($_GET['oauth_verifier'])) {$oauthreturn=TRUE;}
else {$oauthreturn=FALSE;}
if (isset($_GET['logout']) || !$oauthreturn) {//if logout is set, don't reset the session
	session_name('UTRSLogin');
	session_start();
}

if ( isset( $_GET['oauth_verifier'] ) && $_GET['oauth_verifier'] ) {
    $gTokenKey = $_SESSION['tokenKey'];
    $gTokenSecret = $_SESSION['tokenSecret'];
    fetchAccessToken();
    $payload = doIdentify($_GET['wiki']);
	global $wiki;
	if ($wiki === "enwiki") {
    	$is_admin = in_array("sysop", $payload->groups);
		$is_check = in_array("checkuser", $payload->groups);
		$is_os = in_array("oversight", $payload->groups);
		$is_wmf = FALSE;
	}
	else if ($wiki === "meta") {
		if (in_array("wmf-supportsafety", $payload->groups) == TRUE) {
			$is_admin = in_array("wmf-supportsafety", $payload->groups);
		}
		else if (in_array("steward", $payload->groups) == TRUE) {
			$is_admin = in_array("steward", $payload->groups);
		}
		$is_check = FALSE;
		$is_os = FALSE;
		$is_wmf = in_array("wmf-supportsafety", $payload->groups);
	}
    
	$is_blocked = $payload->blocked;
    $username = $payload->username;
	global $CONFIG;
	if ($is_blocked) {
		$errors = 'You are currently blocked from editing. To appeal your block, please use <a href="'.$CONFIG['site_root'].'">the appeal form</a>';
	}
    else if (
	($is_admin === TRUE && $payload->confirmed_email === TRUE) || //main site
	((strpos($CONFIG['site_root'], 'beta') !== false || strpos($CONFIG['site_root'], 'alpha')) && $payload->confirmed_email === TRUE) //dev branch
	) {
        $_SESSION['user'] = $username;
        $_SESSION['oauth'] = TRUE;

        $db = connectToDB(true);
        $query = $db->prepare('
                SELECT userID FROM user
                WHERE username = :username');

        $result = $query->execute(array(
                ':username'	=> $username));

        if($result === false){
                $error = var_export($query->errorInfo(), true);
                debug('ERROR: ' . $error . '<br/>');
                throw new UTRSDatabaseException($error);
        }
        $data = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($data['userID'] === NULL) {
            $user = new UTRSUser(array(
                'wikiAccount' => $username,
                'diff' => "",
                'username' => $username,
                'email' => $payload->email,
            ), false, array(
                'checkuser' => $is_check,
                'oversighter' => $is_os,
                'wmf' => $is_wmf,
            ));
            debug('object created<br/>');
        } else {
            $user = UTRSUser::getUserById($data['userID']);
            if ($user->isCheckuser() !== $is_check || $user->getEmail() !== $payload->email || $user->isOversighter() !== $is_os || $user->isWMF() !== $is_wmf) {
                // XXX: Logging?
                $query = $db->prepare("
                        UPDATE user
                        SET checkuser = :checkuser,
                            oversighter = :oversighter,
                            wmf = :wmf,
                            email = :email
                        WHERE userID = :userID");
                $result = $query->execute(array(
                        ':checkuser' => (bool)$is_check,
                        ':oversighter' => (bool)$is_os,
                        ':wmf' => (bool)$is_wmf,
                        ':email' => $payload->email,
                        ':userID' => (int)$data['userID']));
                if(!$result){
                    $error = var_export($query->errorInfo(), true);
                    debug('ERROR: ' . $error . '<br/>');
                    throw new UTRSDatabaseException($error);
                }
				else {
					UserMgmtLog::insert("synchronized permissions for", "to match onwiki permissions", "", (int)$data['userID'], UTRSUser::getUserByUsername("UTRS OAuth Bot"));
				}
            }
        }
        header("Location: " . "home.php");
        exit;
    } else if ($is_admin === TRUE) {
        $errors = 'You need to have a confirmed email address set in MediaWiki to use UTRS.';
    } else {
        $errors = 'Only administrators can review requests on UTRS.';
    }

} else if (!$logout) {
    doAuthorizationRedirect();
}

session_write_close();

/* END OAUTH */
// ALL CODE BEYOND THIS POINT IS RETAINED FOR HISTORICAL PURPOSES ONLY
if(isset($_POST['login'])){
	try{
		$db = connectToDB(true);
		// all checks here will be conducted without the use of objects, so as to avoid
		// inadvertent output to the screen
		$user = $_POST['username'];
		$password = hash('sha512', $_POST['password']);

		debug('User: ' . $user . '  Password hash: ' . $password . '<br/>');

		$query = $db->prepare('SELECT passwordHash FROM user WHERE username = :username');

		$result = $query->execute(array(
			':username'	=> $user));

		if($result === false){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}

		$row = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();

		if($row === false){
			throw new UTRSCredentialsException('The username you entered does not exist in our records. '
			. 'You may request an account by clicking the link above.');
		}

		if(strcmp($password, $row['passwordHash']) === 0){
			debug('Building session, password is a match<br/>');
			session_id('UTRSLogin'. time());
			session_name('UTRSLogin');
			session_start();
			$_SESSION['user'] = $user;
			$_SESSION['passwordHash'] = $password;
			$_SESSION['language'] = $_POST['language'];
			
			// now that the session has been started, we can check access...
			if(!verifyAccess($GLOBALS['APPROVED'])){
				// force logout
				$_SESSION['user'] = null;
				$_SESSION['passwordHash'] = null;
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
				);

				session_destroy();
				// send error message
				throw new UTRSCredentialsException('Your account has not yet been approved. All '
				  . 'accounts must be approved by a tool administator for security reasons. '
				  . 'If you have not yet made an edit to your Wikipedia talk page to confirm '
				  . 'your identity, please do so now.');
			}
			if(!verifyAccess($GLOBALS['ACTIVE'])){
				$userObj = getCurrentUser();
				// force logout
				$_SESSION['user'] = null;
				$_SESSION['passwordHash'] = null;
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
				);

				session_destroy();
				// send error message
				throw new UTRSCredentialsException('Your account is currently listed as inactive. '
				  . 'The reason given for disabling your account is: "' . $userObj->getComments() 
				  . '" Please contact a tool administrator to have your account reactivated.');
			}

			header("Location: " . $destination);
			exit;
		}
		else{
			throw new UTRSCredentialsException('The username and password you provided do not match. ' . 
					'<a href="passReset.php">Click here</a> to reset your password.');
		}
	}
	catch(UTRSException $e){
		$errors = $e->getMessage();
	}
}
// if just coming here for the first time, and logged in, go to home/destination
else if(loggedIn()){
	header("Location: " . $destination);
	exit;
}

skinHeader();
?>

<center><b>Unblock Ticket Request System Login</b>

<?php 
if($logout){
	displaySuccess('You have been logged out.');
}
?>

<?php 
if($errors){
	displayError($errors);
}
?>

<!-- keeping just in case
<form name="loginForm" id="loginForm" action="login.php" method="POST"><input id="destination" name="destination" value="<?php echo $destination; ?>" type="hidden"><table>
      <tr>
         <td><label for="username" id="usernameLabel">Username: </label></td>
         <td><input id="username" name="username" type="text" id="username" value="<?php echo $user; ?>"></td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td><label for="password" id="passwordLabel">Password: </label></td>
         <td><input id="password" name="password" type="password" id="password"></td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td><label for="lang" id="languageLabel">Language: </label></td>
         <td><select name="lang"><option value="en" selected><option value="pt"></td>
      </tr>
   </table>
   <input id="login" name="login" value="Login" type="submit">
</form>
<p>You must have cookies enabled in order to log in.</p>

<p><a href="passReset.php">Forgot your password?</a></p>
</div>
-->
</center>
<?php 
skinFooter();
?>
