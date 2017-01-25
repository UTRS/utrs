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
require_once('sitemaintain.php');

checkOnline();

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
$mwOAuthUrl = 'https://en.wikipedia.org/w/index.php?title=Special:OAuth';

/**
 * Set this to the interwiki prefix for the OAuth central wiki.
 */
$mwOAuthIW = 'mw';

/**
 * Set this to the API endpoint
 */
$apiUrl = 'https://en.wikipedia.org/w/api.php';

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
session_name('UTRSLogin');
session_start();

if ( isset( $_GET['oauth_verifier'] ) && $_GET['oauth_verifier'] ) {
    $gTokenKey = $_SESSION['tokenKey'];
    $gTokenSecret = $_SESSION['tokenSecret'];
    fetchAccessToken();
    $payload = doIdentify();

    $is_admin = in_array("sysop", $payload->groups);
    $is_check = in_array("checkuser", $payload->groups);
    $username = $payload->username;

    if ($is_admin === TRUE && $payload->confirmed_email === TRUE) {
        session_name('UTRSLogin');
        session_start();
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
            $user = new User(array(
                'wikiAccount' => $username,
                'diff' => "",
                'username' => $username,
                'email' => $payload->email,
            ), false, array(
                'checkuser' => $is_check,
            ));
            debug('object created<br/>');
        } else {
            $user = User::getUserById($data['userID']);
            if ($user->isCheckuser() !== $is_check || $user->getEmail() !== $payload->email) {
                // XXX: Logging?
                $query = $db->prepare("
                        UPDATE user
                        SET checkuser = :checkuser,
                            email = :email
                        WHERE userID = :userID");
                $result = $query->execute(array(
                        ':checkuser' => (bool)$is_check,
                        ':email' => $payload->email,
                        ':userID' => (int)$data['userID']));
                if(!$result){
                    $error = var_export($query->errorInfo(), true);
                    debug('ERROR: ' . $error . '<br/>');
                    throw new UTRSDatabaseException($error);
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

</center>
<?php 
skinFooter();
?>
