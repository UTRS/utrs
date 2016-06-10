<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/languageCookie.php');
echo checkCookie();
$lang=getCookie();
require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/userObject.php');
require_once('src/appealObject.php');
require_once('template.php');
require_once('src/messages.php');

if(loggedIn()){
	// wrong page, for logged out peeps only
	header("Location: " . getRootURL() . "prefs.php");
}

skinHeader();
$errors = '';

if(isset($_POST['submit'])){
	try{
		if(!isset($_POST['username']) | strlen($_POST['username']) == 0 |
		!isset($_POST['email']) | strlen($_POST['email']) == 0){
			throw new UTRSIllegalModificationException("All fields are required.");
		}

		$username = $_POST['username'];
		$email = $_POST['email'];
		// throws exception if username is invalid
		$user = UTRSUser::getUserByUsername($username);
		if(strcmp($email, $user->getEmail()) != 0){
			throw new UTRSIllegalModificationException("The email address you have provided does not match the one in our records.");
		}
		
		$confirmCode = $user->generateResetInfo();
		$ip = Appeal::getIPFromServer();
		// send email
		$body = "Hello " .  $username . ",\n\n";
		$body .= "Someone from " . $ip . ", probably you, has requested that your password on UTRS " .
		        "be changed. In order to carry out this change, please follow the link below:\n\n";
		$body .= getRootURL() . "passReset.php?user=" . $user->getUserId() . "&confirm=" . $confirmCode . "\n\n";
		$body .= "Once you go to this page, a second email will be sent to you with a randomly-generated " .
			"new password.\n\n";
		$body .= "If you did not request a password reset, please delete this email. Your password has not been " .
			"reset, and you will still be able to log in. The link above will expire after 48 hours.\n\n";
		$body .= "Thank you,\nThe UTRS Developement Team";
		$subject = "UTRS Password Reset Confirmation";
		$from = "From: UTRS Development Team <utrs-developers@googlegroups.com>";
		mail($email, $subject, $body, $from);
	}
	catch(UTRSException $e){
		$errors = $e->getMessage();
	}
}
else if(isset($_GET['confirm'])){
	try{
		if(!isset($_GET['user'])){
			throw new UTRSIllegalModificationException("The link you have accessed is not valid. If you have " .
				"received a password reset email, please copy and paste the link to your browser's address bar " .
				"exactly as it appears in the email.");	
		}
		$user = UTRSUser::getUserById($_GET['user']);
		$user->verifyConfirmation($_GET['confirm']); // throws exceptions if invalid
		
		mt_srand(time());
		// 60466177 = (36^5) + 1
		// 2147483647 = 2^31 - 1 ~= 36^5.99(something)
		$randNum = mt_rand(60466177, 2147483647);
		$password = base_convert($randNum, 10, 36); // gives us a 6 or 7 character alphanumeric password
		$password = "" . $password; // force type conversion
		$passwordHash = hash('sha512', $password);
		$ip = Appeal::getIPFromServer();
		// change password, cheating a bit to get past verification
		$user->setNewPassword($user->getPasswordHash(), $passwordHash);
		// send email
		$body = "Hello " .  $user->getUsername() . ",\n\n";
		$body .= "You have successfully reset your password on UTRS. Your new password is:\n\n";
		$body .= $password . "\n\n";
		$body .= "You may now log in with this password, which you should change immediately to a password " .
		         "of your choosing. If you did not request this password change, then please do so at once " .
		         "to ensure your account's security. After you have done so, please reply to this email to " .
		         "inform us of the problem.\n\n";
		$body .= "Thank you,\nThe UTRS Developement Team";
		$subject = "UTRS Password Reset Complete";
		$from = "From: UTRS Development Team <utrs-developers@googlegroups.com>";
		mail($user->getEmail(), $subject, $body, $from);
		unset($password);
		unset($passwordHash);
		unset($randNum);
	}
	catch(UTRSException $e){
		$errors = $e->getMessage();
	}
}

?>

<h3>Password reset</h3>

<?php 
if($errors){
	displayError($errors);
}else if(isset($_POST['submit'])){
	displaySuccess("A confirmation link has been sent to your email address. Please go to that link within " .
		"48 hours to reset your password.");
}
else if(isset($_GET['confirm'])){
	displaySuccess("A new password has been sent to your email address.");
}
?>

<p>If you have forgotten your password, you can enter your username and email address
here to have a random password emailed to you. This password should be changed as 
soon as you log in for your security.</p>

<form name="passReset" id="passReset" method="POST" action="passReset.php">
<label for="username" name="usernameLabel" id="usernameLabel" class="required">Username: </label><input type="text" name="username" id="username" <?php if(isset($_POST['username'])){ echo 'value="' . $_POST['username'] . '"'; }?> /><br/>
<label for="email" name="emailLabel" id="emailLabel" class="required">Email Address: </label><input type="text" name="email" id="email" <?php if(isset($_POST['email'])){ echo 'value="' . $_POST['email'] . '"'; }?> /><br/>
<input name="submit" id="submit" type="submit" value="Reset My Password"/>
</form>

<?php 

skinFooter();

?>
