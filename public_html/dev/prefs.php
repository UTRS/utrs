<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('../src/statsLib.php');
require_once('template.php');

// make sure user is logged in, if not, kick them out
verifyLogin('prefs.php');
// should be sufficient for checking access; unapproved
// and inactive accounts can't log in

$user = getCurrentUser();

$errors = '';
$success = false;

try{
	// handle general preference updates
	if(isset($_POST['submit'])){
		$newEmail = $_POST['email'];
		if($newEmail == ''){
			$newEmail = null;
		}
		
		$newSecure = false;
		if(isset($_POST['secure'])){
			$newSecure = true;
		}
		
		// handles setting stuff that needs to be set.
		// will throw an exception if any problems,
		// so we can keep going assuming it worked
		// because we're in a try block.
		$user->setNewPreferences($newSecure, $newEmail);
		
		$success = true;
	}
	else if(isset($_POST['changePass'])){
		if(strlen($_POST['newPass']) < 4){
			throw new UTRSIllegalModificationException('Passwords must be at least 4 characters long.');
		}
		
		$newPass = hash('sha512', $_POST['newPass']);
		$confirm = hash('sha512', $_POST['confirm']);
		
		if($newPass === $confirm){
			$oldPass = hash('sha512', $_POST['oldPass']);
			
			// As above, this handles issues, so we'll
			// assume it works here.
			$user->setNewPassword($oldPass, $newPass);
			
			// Set new password hash on session cookie, otherwise user gets logged out
			$_SESSION['passwordHash'] = $newPass;
			
			$success = true;
		}
		else{
			throw new UTRSIllegalModificationException('Your new password does not match the confirmation password.');
		}
	}
}
catch(UTRSException $e){
	$errors = $e->getMessage();
}

// will grab new values if there are new values
$secure = $user->getUseSecure();
$secureString = '';
if($secure){
	$secureString = 'checked="true"';
}
$email = $user->getEmail();

//Template header()
skinHeader();

if($errors){
	displayError($errors . '<br/><b>Your preferences have NOT been updated.</b>');
}
else if($success){
	displaySuccess('Your preferences have been updated.');
}
?>

<h2>General settings</h2>

<?php 
echo "<form name=\"generalPrefs\" id=\"generalPrefs\" action=\"prefs.php\" method=\"POST\">";
echo "<input type=\"checkbox\" name=\"secure\" id=\"secure\" " . $secureString . 
	" /> <label for=\"secure\" id=\"secureLabel\">Enable use of the (new) secure server</label><br/>\n";
echo "<label for=\"email\" id=\"emailLabel\">Your email address:</label> <input type=\"text\" name=\"email\" id=\"email\" size=\"40\" value=\"" . $email . "\" /><br/>\n";
echo "<input type=\"submit\" id=\"submit\" name=\"submit\" value=\"Submit\" /> <input type=\"reset\" name=\"reset\" id=\"reset\" value=\"Reset\" />\n";
echo "</form>";
?>

<p>Note: If your Wikipedia account is renamed, you must create a new UTRS account or contact a tool developer to have
your Wikipedia username changed in the database.</p>

<h2>Change your password</h2>

<?php 
echo "<form name=\"pwdChange\" id=\"pwdChange\" action=\"prefs.php\" method=\"POST\">";
echo "<table style=\"background:none; border:none;\" cellspacing=\"2px\">\n";
echo "<tr><td><label for=\"oldPass\" id=\"oldPassLabel\">Your current password:</label></td><td><input type=\"password\" name=\"oldPass\" id=\"oldPass\" /></td></tr>\n";
echo "<tr><td><label for=\"newPass\" id=\"newPassLabel\">Your new password:</label></td><td><input type=\"password\" name=\"newPass\" id=\"newPass\" /></td></tr>\n";
echo "<tr><td><label for=\"confirm\" id=\"confirmLabel\">Confirm your new password:</label></td><td><input type=\"password\" name=\"confirm\" id=\"confirm\" /></td></tr>\n";
echo "</table>";
echo "<input type=\"submit\" id=\"changePass\" name=\"changePass\" value=\"Change Password\" /> <input type=\"reset\" name=\"reset\" id=\"reset\" value=\"Reset\" />\n";
echo "</form>";

skinFooter();

?>