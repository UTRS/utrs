<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/userObject.php');
require_once('../src/appealObject.php');
require_once('template.php');

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
		$user = User::getUserByUsername($username);
		if(strcmp($email, $user->getEmail()) != 0){
			throw new UTRSIllegalModificationException("The email address you have provided does not match the one in our records.");
		}
		
		mt_srand(time());
		// 60466177 = (36^5) + 1
		// 78364164096 = (36^7)
		$randNum = mt_rand(60466177, 78364164096);
		$password = base_convert($randNum, 10, 36); // gives us a 6 or 7 character alphanumeric password
		$password = "" . $password; // force type conversion
		$passwordHash = hash('sha512', $password);
		$ip = Appeal::getIPFromServer();
		// change password, cheating a bit to get past verification
		$user->setNewPassword($user->getPasswordHash(), $passwordHash);
		// send email
		$body = "Hello " .  $username . ",\n\n";
		$body .= "Someone from " . $ip . ", probably you, has requested that your password on UTRS " .
		        "be changed. This has been done, and your password is now:\n\n";
		$body .= $password . "\n\n";
		$body .= "You may now log in with this password, which you should change immediately to a password " .
		         "of your choosing. If you did not request this password change, then please do so at once " .
		         "to ensure your account's security. After you have done so, please reply to this email to " .
		         "inform us of the problem.\n\n";
		$body .= "Thank you,\nThe UTRS Developement Team";
		$subject = "UTRS Password Reset";
		$from = "From: UTRS Development Team <unblock@toolserver.org>";
		mail($mail, $subject, $body, $from);
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
	displaySuccess("Your password has been reset. Please check your email for your new password.");
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