<?php
## THIS SECTION MUST NOT SEND ANY OUTPUT TO THE SCREEN. ##
##      DOING SO WILL CAUSE THE REDIRECTION TO FAIL.    ##
##      THIS INCLUDES ALL USE OF THE debug() METHOD.    ##

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
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

if(isset($_POST['login'])){
	// all checks here will be conducted without the use of objects, so as to avoid
	// inadvertent output to the screen
	$user = $_POST['username'];
	$password = hash('sha512', $_POST['password']);

	debug('User: ' . $user . '  Password hash: ' . $password . '<br/>');
	
	try{
		$db = connectToDB(true);

		$query = 'SELECT passwordHash FROM user WHERE username=\'' . $user . '\'';

		$result = mysql_query($query, $db);

		if($result === false){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}

		if(mysql_num_rows($result) == 0){
			throw new UTRSCredentialsException('The username you entered does not exist in our records. '
			. 'You may request an account by clicking the link above.');
		}
		if(mysql_num_rows($result) != 1){
			throw new UTRSDatabaseException('There is more than one record for that username. '
			. 'Please contact a tool developer immediately.');
		}
		else{
			$row = mysql_fetch_assoc($result);

			if(strcmp($password, $row['passwordHash']) === 0){
				debug('Building session, password is a match<br/>');
				session_id('UTRSLogin');
				session_name('UTRSLogin');
				session_start();
				$_SESSION['user'] = $user;
				$_SESSION['passwordHash'] = $password;

				header("Location: " . $destination);
				exit;
			}
			else{
				throw new UTRSCredentialsException('The username and password you provided do not match.');
			}
		}
	}
	catch(UTRSException $e){
		$errors = $e->getMessage();
	}
}

skinHeader();
?>

<center><b>Unblock Ticket Request System Login</b></center>

<?php 
if($logout){
	echo '<p><b>You have been logged out.</b></p>';
}
?>

<p>If you do not already have an UTRS account, please <a href="register.php">register here</a>.</p>

<?php 
if($errors){
	echo '<div class="error">' . $errors . '</div>';
}
echo '<form name="loginForm" id="loginForm" action="login.php" method="POST">';
echo '<input type="hidden" id="destination" name="destination" value="' . $destination . '" />';
echo '<label for="username" id="usernameLabel">Username: </label> <input type="text" id="username" name="username" value="' . $user . '" /><br />';
echo '<label for="password" id="passwordLabel">Password: </label> <input type="password" id="password" name="password" /><br />';
echo '<input type="submit" id="login" name="login" value="Login" />';
echo '</form>';
?>

<p>You must have cookies enabled in order to log in.</p>

<?php 
skinFooter();
?>