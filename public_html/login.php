<?php
## THIS SECTION MUST NOT SEND ANY OUTPUT TO THE SCREEN. ##
##      DOING SO WILL CAUSE THE REDIRECTION TO FAIL.    ##
##      THIS INCLUDES ALL USE OF THE debug() METHOD.    ##

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/unblocklib.php');
require_once('template.php');

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
if ( isset( $_SESSION['tokenKey'] ) ) {
    $gTokenKey = $_SESSION['tokenKey'];
    $gTokenSecret = $_SESSION['tokenSecret'];
}
session_write_close();

if ( isset( $_GET['oauth_verifier'] ) && $_GET['oauth_verifier'] ) {
        fetchAccessToken();
}

// Take any requested action
switch ( isset( $_GET['action'] ) ? $_GET['action'] : '' ) {
    case 'download':
        header( 'Content-Type: text/plain' );
        readfile( __FILE__ );
        return;

    case 'authorize':
        doAuthorizationRedirect();
        return;

    case 'edit':
        doEdit();
        break;

    case 'identify':
        doIdentify();
        break;

    case 'testspecial':
        doTestSpecial();
        break;
}

// ******************** CODE ********************


/**
 * Utility function to sign a request
 *
 * Note this doesn't properly handle the case where a parameter is set both in 
 * the query string in $url and in $params, or non-scalar values in $params.
 *
 * @param string $method Generally "GET" or "POST"
 * @param string $url URL string
 * @param array $params Extra parameters for the Authorization header or post 
 *      data (if application/x-www-form-urlencoded).
 * @return string Signature
 */
function sign_request( $method, $url, $params = array() ) {
    global $gConsumerSecret, $gTokenSecret;

    $parts = parse_url( $url );

    // We need to normalize the endpoint URL
    $scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : 'http';
    $host = isset( $parts['host'] ) ? $parts['host'] : '';
    $port = isset( $parts['port'] ) ? $parts['port'] : ( $scheme == 'https' ? '443' : '80' );
    $path = isset( $parts['path'] ) ? $parts['path'] : '';
    if ( ( $scheme == 'https' && $port != '443' ) ||
        ( $scheme == 'http' && $port != '80' ) 
    ) {
        // Only include the port if it's not the default
        $host = "$host:$port";
    }

    // Also the parameters
    $pairs = array();
    parse_str( isset( $parts['query'] ) ? $parts['query'] : '', $query );
    $query += $params;
    unset( $query['oauth_signature'] );
    if ( $query ) {
        $query = array_combine(
            // rawurlencode follows RFC 3986 since PHP 5.3
            array_map( 'rawurlencode', array_keys( $query ) ),
            array_map( 'rawurlencode', array_values( $query ) )
        );
        ksort( $query, SORT_STRING );
        foreach ( $query as $k => $v ) {
            $pairs[] = "$k=$v";
        }
    }

    $toSign = rawurlencode( strtoupper( $method ) ) . '&' .
        rawurlencode( "$scheme://$host$path" ) . '&' .
        rawurlencode( join( '&', $pairs ) );
    $key = rawurlencode( $gConsumerSecret ) . '&' . rawurlencode( $gTokenSecret );
    return base64_encode( hash_hmac( 'sha1', $toSign, $key, true ) );
}

/**
 * Request authorization
 * @return void
 */
function doAuthorizationRedirect() {
    global $mwOAuthUrl, $mwOAuthAuthorizeUrl, $gUserAgent, $gConsumerKey, $gTokenSecret;

    // First, we need to fetch a request token.
    // The request is signed with an empty token secret and no token key.
    $gTokenSecret = '';
    $url = $mwOAuthUrl . '/initiate';
    $url .= strpos( $url, '?' ) ? '&' : '?';
    $url .= http_build_query( array(
        'format' => 'json',
        
        // OAuth information
        'oauth_callback' => 'oob', // Must be "oob" for MWOAuth
        'oauth_consumer_key' => $gConsumerKey,
        'oauth_version' => '1.0',
        'oauth_nonce' => md5( microtime() . mt_rand() ),
        'oauth_timestamp' => time(),

        // We're using secret key signatures here.
        'oauth_signature_method' => 'HMAC-SHA1',
    ) );
    $signature = sign_request( 'GET', $url );
    $url .= "&oauth_signature=" . urlencode( $signature );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    //curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_USERAGENT, $gUserAgent );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $data = curl_exec( $ch );
    if ( !$data ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
        exit(0);
    }
    curl_close( $ch );
    $token = json_decode( $data );
    if ( is_object( $token ) && isset( $token->error ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Error retrieving token: ' . htmlspecialchars( $token->error );
        exit(0);
    }
    if ( !is_object( $token ) || !isset( $token->key ) || !isset( $token->secret ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Invalid response from token request';
        exit(0);
    }

    // Now we have the request token, we need to save it for later.
    session_name('UTRSLogin');
    session_start();
    $_SESSION['tokenKey'] = $token->key;
    $_SESSION['tokenSecret'] = $token->secret;
    session_write_close();

    // Then we send the user off to authorize
    $url = $mwOAuthAuthorizeUrl;
    $url .= strpos( $url, '?' ) ? '&' : '?';
    $url .= http_build_query( array(
        'oauth_token' => $token->key,
        'oauth_consumer_key' => $gConsumerKey,
    ) );
    header( "Location: $url" );
    echo 'Please see <a href="' . htmlspecialchars( $url ) . '">' . htmlspecialchars( $url ) . '</a>';
}

/**
 * Handle a callback to fetch the access token
 * @return void
 */
function fetchAccessToken() {
    global $mwOAuthUrl, $gUserAgent, $gConsumerKey, $gTokenKey, $gTokenSecret;

    $url = $mwOAuthUrl . '/token';
    $url .= strpos( $url, '?' ) ? '&' : '?';
    $url .= http_build_query( array(
        'format' => 'json',
        'oauth_verifier' => $_GET['oauth_verifier'],

        // OAuth information
        'oauth_consumer_key' => $gConsumerKey,
        'oauth_token' => $gTokenKey,
        'oauth_version' => '1.0',
        'oauth_nonce' => md5( microtime() . mt_rand() ),
        'oauth_timestamp' => time(),

        // We're using secret key signatures here.
        'oauth_signature_method' => 'HMAC-SHA1',
    ) );
    $signature = sign_request( 'GET', $url );
    $url .= "&oauth_signature=" . urlencode( $signature );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    //curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_USERAGENT, $gUserAgent );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $data = curl_exec( $ch );
    if ( !$data ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
        exit(0);
    }
    curl_close( $ch );
    $token = json_decode( $data );
    if ( is_object( $token ) && isset( $token->error ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Error retrieving token: ' . htmlspecialchars( $token->error );
        exit(0);
    }
    if ( !is_object( $token ) || !isset( $token->key ) || !isset( $token->secret ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Invalid response from token request';
        exit(0);
    }

    // Save the access token
    session_name('UTRSLogin');
    session_start();
    $_SESSION['tokenKey'] = $gTokenKey = $token->key;
    $_SESSION['tokenSecret'] = $gTokenSecret = $token->secret;
    session_write_close();
}


/**
 * Send an API query with OAuth authorization
 *
 * @param array $post Post data
 * @param object $ch Curl handle
 * @return array API results
 */
function doApiQuery( $post, &$ch = null ) {
    global $apiUrl, $gUserAgent, $gConsumerKey, $gTokenKey;

    $headerArr = array(
        // OAuth information
        'oauth_consumer_key' => $gConsumerKey,
        'oauth_token' => $gTokenKey,
        'oauth_version' => '1.0',
        'oauth_nonce' => md5( microtime() . mt_rand() ),
        'oauth_timestamp' => time(),

        // We're using secret key signatures here.
        'oauth_signature_method' => 'HMAC-SHA1',
    );
    $signature = sign_request( 'POST', $apiUrl, $post + $headerArr );
    $headerArr['oauth_signature'] = $signature;

    $header = array();
    foreach ( $headerArr as $k => $v ) {
        $header[] = rawurlencode( $k ) . '="' . rawurlencode( $v ) . '"';
    }
    $header = 'Authorization: OAuth ' . join( ', ', $header );

    if ( !$ch ) {
        $ch = curl_init();
    }
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_URL, $apiUrl );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $post ) );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $header ) );
    //curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_USERAGENT, $gUserAgent );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $data = curl_exec( $ch );
    if ( !$data ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
        exit(0);
    }
    $ret = json_decode( $data );
    if ( $ret === null ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Unparsable API response: <pre>' . htmlspecialchars( $data ) . '</pre>';
        exit(0);
    }
    return $ret;
}

/**
 * Request a JWT and verify it
 * @return void
 */
function doIdentify() {
    global $mwOAuthUrl, $gUserAgent, $gConsumerKey, $gTokenKey, $gConsumerSecret;

    $url = $mwOAuthUrl . '/identify';
    $headerArr = array(
        // OAuth information
        'oauth_consumer_key' => $gConsumerKey,
        'oauth_token' => $gTokenKey,
        'oauth_version' => '1.0',
        'oauth_nonce' => md5( microtime() . mt_rand() ),
        'oauth_timestamp' => time(),

        // We're using secret key signatures here.
        'oauth_signature_method' => 'HMAC-SHA1',
    );
    $signature = sign_request( 'GET', $url, $headerArr );
    $headerArr['oauth_signature'] = $signature;

    $header = array();
    foreach ( $headerArr as $k => $v ) {
        $header[] = rawurlencode( $k ) . '="' . rawurlencode( $v ) . '"';
    }
    $header = 'Authorization: OAuth ' . join( ', ', $header );

    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $header ) );
    //curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_USERAGENT, $gUserAgent );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $data = curl_exec( $ch );
    if ( !$data ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
        exit(0);
    }
    $err = json_decode( $data );
    if ( is_object( $err ) && isset( $err->error ) && $err->error === 'mwoauthdatastore-access-token-not-found' ) {
        // We're not authorized!
        echo 'You haven\'t authorized this application yet! Go <a href="' . htmlspecialchars( $_SERVER['SCRIPT_NAME'] ) . '?action=authorize">here</a> to do that.';
        echo '<hr>';
        return;
    }

    // There are three fields in the response
    $fields = explode( '.', $data );
    if ( count( $fields ) !== 3 ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Invalid identify response: ' . htmlspecialchars( $data );
        exit(0);
    }

    // Validate the header. MWOAuth always returns alg "HS256".
    $header = base64_decode( strtr( $fields[0], '-_', '+/' ), true );
    if ( $header !== false ) {
        $header = json_decode( $header );
    }
    if ( !is_object( $header ) || $header->typ !== 'JWT' || $header->alg !== 'HS256' ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Invalid header in identify response: ' . htmlspecialchars( $data );
        exit(0);
    }

    // Verify the signature
    $sig = base64_decode( strtr( $fields[2], '-_', '+/' ), true );
    $check = hash_hmac( 'sha256', $fields[0] . '.' . $fields[1], $gConsumerSecret, true );
    if ( $sig !== $check ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'JWT signature validation failed: ' . htmlspecialchars( $data );
        echo '<pre>'; var_dump( base64_encode($sig), base64_encode($check) ); echo '</pre>';
        exit(0);
    }

    // Decode the payload
    $payload = base64_decode( strtr( $fields[1], '-_', '+/' ), true );
    if ( $payload !== false ) {
        $payload = json_decode( $payload );
    }
    if ( !is_object( $payload ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Invalid payload in identify response: ' . htmlspecialchars( $data );
        exit(0);
    }

    $is_admin = in_array("sysop", $payload->groups);
    $username = $payload->username;
    $email = $payload->confirmed_email ? $payload->email : '';

    if ($is_admin == TRUE) {
        session_name('UTRSLogin');
        session_start();
        $_SESSION['user'] = $username;
        $_SESSION['oauth'] = TRUE;
        # XXX
        header("Location: " . "home.php");
        exit;
    }
}

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

<div id="loginBox">
<p>If you do not already have an UTRS account, please <a href="register.php">register here</a>.</p>

<?php 
if($errors){
	displayError($errors);
}
?>

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
   </table>
   <input id="login" name="login" value="Login" type="submit">
</form>
<p>You must have cookies enabled in order to log in.</p>

<p><a href="passReset.php">Forgot your password?</a></p>
</div>
</center>
<?php 
skinFooter();
?>
