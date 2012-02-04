<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('recaptchalib.php');
require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/userObject.php');
require_once('src/logObject.php');

$publickey = '6Le92MkSAAAAANADTBB8wdC433EHXGpuP_v1OaOO';
$privatekey = '6Le92MkSAAAAAH1tkp8sTZj_lxjNyBX7jARdUlZd';
$captchaErr = null;
$errorMessages = '';

$username = null;
$email = null;
$wikiAccount = null;
$useSecure = null;
$user = null;

// Handle submitted form
if(isset($_POST["submit"])){
	
	debug('form submitted <br/>');
	
	try{
		
		$username = $_POST["username"];
		$email = $_POST["email"];
		$wikiAccount = $_POST["wikiAccount"];
		$useSecure = isset($_POST["useSecure"]);
		
		// verify captcha
		$resp = recaptcha_check_answer($privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]);
			
		if(!$resp->is_valid) {
			$captchaErr = $resp->error;
			$errorMessages = 'The response you provided to the captcha was not correct. Please try again.';
		}
		
		debug('captcha valid <br/>');
		
		$username = TRIM($username);
		$email = TRIM($email);
		$wikiAccount = TRIM($wikiAccount);
		
		if($username === '' || $username == null){
			if($errorMessages != null){
				$errorMessages .= '<br/>';
			}
			$errorMessages .= 'Username is required.';
		}
		if($_POST['password'] === '' || $_POST['password'] == null){
			if($errorMessages != null){
				$errorMessages .= '<br/>';
			}
			$errorMessages .= 'A password is required.';
		}
		else if(strlen($_POST['password']) < 4){
			if($errorMessages != null){
				$errorMessages .= '<br/>';
			}
			$errorMessages .= 'Passwords must be at least 4 characters long.';
		}
		if($email === '' || $email == null){
			if($errorMessages != null){
				$errorMessages .= '<br/>';
			}
			$errorMessages .= 'A valid email address is required.';
		}
		if($wikiAccount === '' || $wikiAccount == null){
			if($errorMessages != null){
				$errorMessages .= '<br/>';
			}
			$errorMessages .= 'The name of your Wikipedia account is required.';
		}
		if(strpos($username, "#") !== false | strpos($username, "/") !== false |
		   strpos($username, "|") !== false | strpos($username, "[") !== false |
		   strpos($username, "]") !== false | strpos($username, "{") !== false |
		   strpos($username, "}") !== false | strpos($username, "<") !== false |
		   strpos($username, ">") !== false | strpos($username, "@") !== false |
		   strpos($username, "%") !== false | strpos($username, ":") !== false | 
		   strpos($username, '$') !== false){
		   	$errorMessages .= 'The username you have entered is invalid. Usernames ' .
		   	 	'may not contain the characters # / | [ ] { } < > @ % : $';
		}
		
		if(!$errorMessages){
			$user = new User($_POST, false);
			debug('object created<br/>');
			Log::ircNotification("\x033,0New user account \x032,0" . $user->getUsername() . "\x033,0 has been requested. URL: " . getRootURL() . "userMgmt.php?userId=" . $user->getUserId());
		}
	}
	catch(UTRSException $ex){
		$errorMessages = $ex->getMessage() . '<br />' . $errorMessages;
	}
	catch(ErrorException $ex){
		$errorMessages = 'An error occured: ' . $ex->getMessage() . '<br />' . $errorMessages;
	}
	
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
<link rel="stylesheet" href="unblock_styles.css">
<title>Unblock Ticket Request System - Register an Account</title>

</head>
<body>
<div id="header">
English Wikipedia<br />
Unblock Ticket Request System
</div>
<div id="subheader">
<table class="subheader_content">
<tr>
<td>
<a id="appealForm" href="index.php">Appeal a Block</a>
</td>
<td>
<a id="GAB" href="http://en.wikipedia.org/wiki/Wikipedia:Guide_to_appealing_blocks">Guide to Appealing Blocks</a>
</td>
<td>
<a id="loginLink" href="login.php">Admins: Log in to review requests</a>
</td>
<td>
<a id="privacyPolicy" href="privacy.html">Privacy Policy</a>
</td>
</tr>
</table>
</div>
<div id="main">
<center><b>Welcome to the Unblock Ticket Request System.</b></center>

<p>Administrators who wish to assist in reviewing blocks through this system may register
an account here. Your account must be approved by a tool administrator before it may 
be used on this site. If you have already requested an account, but it has gone unapproved
for some time, please email the development team at 
<a href="mailto:unblock@toolserver.org?subject=Account approval">unblock@toolserver.org</a>.</p>

<p>After completing this form, please add a new section to your user talk page, with the
heading "UTRS Account Request", while logged into your administrator account in order
to confirm that you have requested an account here. A link will be provided for this 
purpose once your account has been created. Thank you.</p>

<?php 
if($errorMessages){
	displayError($errorMessages);
}
if($user != null){
	echo '<center><b>Thank you, ' . $user->getUsername() . '. Your account has been created ';
	echo 'and is awaiting approval by a tool administrator.</b></center>';
	echo '<p>To complete the registration process, please click on the link below ';
	echo 'to edit your user talk page and confirm your creation of an account. ';
	echo 'Without this verification, your account <i>will not</i> be approved. ';
	echo 'Please leave this section on your talk page until you receive notice that ';
	echo 'your account is approved; if you use an archival bot, you may wish to ';
	echo 'disable it temporarily or remove timestamps from this section.</p>';
	echo '<center><b><a href="' . getWikiLink('User talk:' . $user->getWikiAccount(), 
											  $user->getUseSecure(), 
											  'action=edit&section=new'
											   . '&preloadtitle=UTRS%20Account%20Request'
											   . '&preload=User:Hersfold/UTRSpreload');	
	echo '">Edit your talk page to confirm your request</a></b></center>';
	echo '<br/>';
}
else{
	echo '<p>Fields in red are required. Passwords must be at least four characters in length.</p>';
	echo '<form name="accountRegister" id="accountRegister" action="register.php" method="POST">';
	echo '<label id="usernameLabel" for="username" class="required">Username:</label> <input id="username" type="text" name="username" value="' . $username . '"/><br/><br/>';
	echo '<label id="passwordLabel" for="password" class="required">Password:</label> <input id="password" type="password" name="password" "/><br/><br/>';
	echo '<label id="wikiAccountLabel" for="wikiAccount" class="required">Wikipedia username:</label> <input id="wikiAccount" type="text" name="wikiAccount" value="' . $wikiAccount . '"/><br/><br/>';
	echo '<label id="emailLabel" for="email" class="required">Email address:</label> <input id="email" type="text" name="email" value="' . $email . '"/><br/><br/>';
	echo '<label id="useSecureLabel" for="useSecure">Do you want links to Wikipedia to use the secure server?</label> <input id="useSecure" type="checkbox" name="useSecure" ' . ($useSecure ? 'checked="true"' : '') . '/><br/><br/>';
	
	echo '<span class="overridePre">';
	if($captchaErr == null){
		echo recaptcha_get_html($publickey);
	}
	else{
		echo recaptcha_get_html($publickey, $captchaErr);
	}
	echo '</span>';
	
	echo '<p>By registering an account, you are consenting to allow us to collect and store your email ' .
	 'address. We will not share it with any third party. For more information, please see our ' .
	 '<a href="privacy.html">Privacy Policy.</a></p>';

	echo '<input type="submit" name="submit" value="Register"/>';
	echo '</form>';
}
?>
<br />

</div>
<div id="footer">
The Unblock Ticket Request System is a project hosted on the Wikimedia Toolserver intended to assist
users with the unblock process on the English Wikipedia. <br />
This project is licensed under the 
<a id="GPL" href="http://www.gnu.org/copyleft/gpl.html">GNU General Public License Version 3 or Later.</a><br />
For questions or assistance with the Unblock Ticket Request System, please email our development team at 
<a href="mailto:unblock@toolserver.org">unblock AT toolserver DOT org</a><br />
</div>
</body>
</html>