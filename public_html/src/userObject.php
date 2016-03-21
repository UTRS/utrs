<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('exceptions.php');
require_once('unblocklib.php');
require_once('userMgmtLogObject.php');


class User{
	
	private $username;
	private $userId;
	private $email;
	private $wikiAccount;
	private $approved;
	private $active;
	private $toolAdmin;
	private $checkuser;
	private $wmf;
	private $oversight;
	private $developer;
	private $useSecure;
	private $acceptToS;
	private $replyNotify;
	private $passwordHash;
	private $comments;
	private $registered;
	private $closed;
	
	public function __construct(array $vars, $fromDB){
		debug('in constructor for user <br/>');
		if($fromDB){
			$this->username = $vars['username'];
			$this->userId = $vars['userID'];
			$this->email = $vars['email'];
			$this->wikiAccount = $vars['wikiAccount'];
			$this->approved = ($vars['approved'] == 1 || $vars['approved'] == '1' ? true : false);
			$this->active = ($vars['active'] == 1 || $vars['active'] == '1' ? true : false);
			$this->toolAdmin = ($vars['toolAdmin'] == 1 || $vars['toolAdmin'] == '1' ? true : false);
			$this->checkuser = ($vars['checkuser'] == 1 || $vars['checkuser'] == '1' ? true : false);
			$this->developer = ($vars['developer'] == 1 || $vars['developer'] == '1' ? true : false);
			$this->oversight = ($vars['oversighter'] == 1 || $vars['oversighter'] == '1' ? true : false);
			$this->wmf = ($vars['wmf'] == 1 || $vars['wmf'] == '1' ? true : false);
			$this->passwordHash = $vars['passwordHash'];
			$this->useSecure = True;
			$this->acceptToS = ($vars['acceptToS'] == 1 || $vars['acceptToS'] == '1' ? true : false);
			$this->replyNotify = $vars['replyNotify'];
			$this->comments = $vars['comments'];
			$this->registered = $vars['registered'];
			$this->closed = $vars['closed'];
			$this->diff = $vars['diff'];
		}
		else{
			$this->username = $vars['username'];
			$this->email = $vars['email'];
			$this->wikiAccount = $vars['wikiAccount'];
			$this->approved = 0;
			$this->active = 0;
			$this->toolAdmin = 0;
			$this->checkuser = 0;
			$this->developer = 0;
			$this->oversight = 0;
			$this->wmf = 0;
			$this->useSecure = True;
			$this->acceptToS = 1;
			$this->replyNotify = 1;
			$this->passwordHash = hash("sha512", $vars['password']);
			$this->closed = 0;
			$this->diff = $vars['diff'];
			
			$this->insert();
		}
		debug('leaving user constructor <br/>');
	}
	
	public function insert(){
		debug('in insert for User <br />');
		
		$db = connectToDB();

		$query = $db->prepare('
			INSERT INTO user (username, email, wikiAccount, useSecure, passwordHash, diff, acceptToS)
			VALUES (:username, :email, :wikiAccount, :useSecure, :passwordHash, :diff, 1)');

		$result = $query->execute(array(
			':username'	=> $this->username,
			':email'	=> $this->email,
			':wikiAccount'	=> $this->wikiAccount,
			':useSecure'	=> $this->useSecure,
			':passwordHash'	=> $this->passwordHash,
			':diff'		=> $this->diff));

		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		debug('Insert complete <br/>');

		$query = $db->prepare("SELECT userID, registered FROM user WHERE username = :username");

		$result = $query->execute(array(
			':username'	=> $this->username));

		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}

		$row = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();

		if ($row === false) {
			throw new UTRSDatabaseException('Unable to fetch newly-inserted user record.');
		}
		
		$this->userId = $row['userID'];
		$this->registered = $row['registered'];
		
		UserMgmtLog::insert('created account', 'Requested new account', 'New account', $this->userId, $this->userId, 0);
		
		debug('exiting user insert <br/>');
	}
	
	public static function getUserById($id){
		$db = connectToDB();
		
		$query = $db->prepare('SELECT * FROM user WHERE userID = :userID');
		$result = $query->execute(array(
			':userID'	=> $id));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}

		$values = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();

		if ($values === false) {
			throw new UTRSDatabaseException('No results were returned for user ID ' . $id);
		}
		
		return new User($values, true);
	}
	
	public static function getUserByUsername($username){
		$db = connectToDB();
		
		$query = $db->prepare('SELECT * FROM user WHERE username = :username');
		
		$result = $query->execute(array(
			':username'	=> $username));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}

		$values = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();

		if ($values === false) {
			throw new UTRSDatabaseException('No results were returned for username ' . $username);
		}
		
		return new User($values, true);
	}
	
	public function getUserId() {
		return $this->userId;
	}
	
	public function getUsername(){
		return $this->username;
	}
	
	public function getWikiAccount(){
		return $this->wikiAccount;
	}
	
	public function getUseSecure(){
		return $this->useSecure;
	}
	
	public function getProtocol() {
		if ($this->useSecure) {
			return "http";
		} else {
			return "https";
		}
	}
	
	public function getEmail(){
		return $this->email;
	}
	
	public function getPasswordHash(){
		return $this->passwordHash;
	}
	
	public function isApproved(){
		return $this->approved;
	}
	
	public function isActive(){
		return $this->active;
	}
	
	public function isAdmin(){
		return $this->toolAdmin;
	}
	
	public function isCheckuser(){
		return $this->checkuser;
	}
	
	public function isOversighter(){
		return $this->oversight;
	}
	
	public function isWMF(){
		return $this->wmf;
	}
	
	public function isDeveloper(){
		return $this->developer;
	}
	
	public function getComments(){
		return $this->comments;
	}
	
	public function getRegistered(){
		return $this->registered;
	}
	
	public function getAcceptToS() {
		return $this->acceptToS; 
	}
	
	public function setAcceptToS() {
		
		$db = connectToDB();

		$query = $db->prepare("
			UPDATE user
			SET acceptToS = 1
			WHERE userID = :userID");

		$result = $query->execute(array(
			':userID'	=> $this->userId));

		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->acceptToS = 1;
		
	}
	
	public function replyNotify() {	
		return $this->replyNotify;
	}
	
	public function getClosed() {
		return $this->closed;
	}
	
	public function getDiff(){
		return $this->diff;
	}
	
	public function setNewPreferences($newSecure, $newEmail, $newReply){
		if($newEmail != null & !validEmail($newEmail)){
			throw new UTRSIllegalModificationException('The email address you have entered (' . $newEmail . ') is invalid.');
		}
		if(($newEmail == null | ($newEmail != null & $newEmail == $this->email)) & $newSecure == $this->useSecure & $newReply == $this->replyNotify){
			throw new UTRSIllegalModificationException('You have not changed any of your preferences.');
		}
		
		// ok to change	
		$secureInt = 0;	
		if($newSecure){
			$secureInt = 1;
		}
		$replyNotify = 0;
		if ($newReply) {
			$replyNotify = 1;
		}

		$db = connectToDB();

		$query = $db->prepare("
			UPDATE user
			SET useSecure = :useSecure,
			    email = :email,
			    replyNotify = :replyNotify
			WHERE userID = :userID");

		$result = $query->execute(array(
			':useSecure'	=> $secureInt,
			':email'	=> $newEmail,
			':replyNotify'	=> $replyNotify,
			':userID'	=> $this->userId));

		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->email = $newEmail;
		$this->useSecure = $newSecure;
		$this->replyNotify = $newReply;
	}
	
	public function setNewPassword($oldpass, $newpass){
		if($oldpass == null | $oldpass != $this->passwordHash){
			throw new UTRSIllegalModificationException("Your current password is incorrect.");
		}
		
		// ok to update
		$db = connectToDB();

		$query = $db->prepare("
			UPDATE user
			SET passwordHash = :passwordHash,
			    resetConfirm = NULL,
			    resetTime = NULL
			WHERE userID = :userID");

		$result = $query->execute(array(
			':passwordHash'	=> $newpass,
			':userID'	=> $this->userId));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
	}
	
	public function approve($admin){
		$db = connectToDB();

		$query = $db->prepare("UPDATE user SET approved = 1 WHERE userID = :userID");
		
		$result = $query->execute(array(
			':userID'	=> $this->userId));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->approved = true;
		
		UserMgmtLog::insert("approved account", "", "Autorized", $this->userId, $admin->userId);
		
		
		$emailBody = "Hello " . $this->username . ", \n\n" .
								"This is a notification that your account has been approved on the " .
								"Unblock Ticket Request System (UTRS) by " . $admin->getUsername() . ".  Please login " .
								"to <a href=\"" . getRootURL() . "\">the system</a> to begin reviewing " .
								"unblock requests.  Thanks for volunteering!";
		
		$headers = "From: UTRS Development Team <unblock@toolserver.org>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
		// notify user
		mail($this->email, "UTRS account approved", $emailBody, $headers);
	}
	
	public function disable($admin, $comments){
		$db = connectToDB();
		
		$query = $db->prepare("
			UPDATE user
			SET active = 0,
			    comments = :comments
			WHERE userID = :userID");

		$result = $query->execute(array(
			':comments'	=> $comments,
			':userID'	=> $this->userId));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->active = false;
		$this->comments = $comments;
		
		UserMgmtLog::insert("disabled account", "Account deactivated", $comments, $this->userId, $admin->userId);
		
		$emailBody = "Hello " . $this->username . ",\n\nThis is to notify you that your account on the Unblock " .
		    "Ticket Request System has been disabled by " . $admin->getUsername() . ". The reason given for this " .
		    "action is: \"" . $comments . "\". You may contact any tool administrator to have your account " .
		    "reactivated.\n\nSincerely,\nThe UTRS Development Team";
		
		// notify user
		mail($this->email, "UTRS account disabled", $emailBody, "From: UTRS Development Team <unblock@toolserver.org>");
	}
	
	public function enable($admin){
		$db = connectToDB();
		
		$query = $db->prepare("
			UPDATE user
			SET active = 1,
			    comments = NULL
			WHERE userID = :userID");

		$result = $query->execute(array(
			':userID'	=> $this->userId));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->active = true;
		$this->comments = null;
		
		UserMgmtLog::insert("enabled account", "Account Enabled" , $comments, $this->userId, $admin->userId);
	}
	
	public function setPermissions($adminFlag, $devFlag, $cuFlag, $admin, $wmfFlag, $oversightFlag, $reason){
		// safety checks
		$adminFlag = (bool)$adminFlag;
		$devFlag = (bool)$devFlag;
		$cuFlag = (bool)$cuFlag;
		$wmfFlag = (bool)$wmfFlag;
		$oversightFlag = (bool)$oversightFlag;
		
		$db = connectToDB();
		
		$query = $db->prepare("
			UPDATE user
			SET toolAdmin = :toolAdmin,
			    developer = :developer,
			    checkuser = :checkuser,
				oversighter = :oversighter,
				wmf = :wmf
			WHERE userID = :userID");

		$result = $query->execute(array(
			':toolAdmin'	=> $adminFlag,
			':developer'	=> $devFlag,
			':checkuser'	=> $cuFlag,
			':oversighter'	=> $oversightFlag,
			':wmf'	=> $wmfFlag,
			':userID'	=> $this->userId));

		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->toolAdmin = $adminFlag;
		$this->checkuser = $cuFlag;
		$this->developer = $devFlag;
		$this->wmf = $wmfFlag;
		$this->oversight = $oversightFlag;
		
		UserMgmtLog::insert("changed permissions for", 
					"to" . 
							($adminFlag == TRUE)? "Administrator ":"". ($cuFlag == TRUE)? "Checkuser ":"". ($devFlag == TRUE)? "Developer ":"". ($wmfFlag == TRUE)? "WMF Staff ":"". ($oversightFlag == TRUE)? "Oversight ":"",
					$reason, $this->userId, $admin->userId);
	}
	
	
	public function renameUser($newName, $admin){
		if($admin->getUserId() == $this->getUserId()){
			throw new UTRSIllegalModificationException("To avoid errors, administrators may not " . 
				"rename themselves. Please contact another tool administrator to correct your name.");
		}
		
		$oldName = $this->getUsername();
		
		$db = connectToDB();
		
		$query = $db->prepare("
			UPDATE user
			SET username = :username
			WHERE userID = :userID");

		$result = $query->execute(array(
			':username'	=> $newName,
			':userID'	=> $this->userId));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		UserMgmtLog::insert("renamed user to \"". $newName . "\"", "Remamed user", "Old username: \"" . $oldName . "\"", $this->getUserId(), $admin->getUserId(), true);
		
		Log::ircNotification("\x032" . $oldName . "\x033 has been renamed to \x032" . $newName . 
		    "\x033 by \x032" . $admin->getUsername(), 1);
	}
	
	public function incrementClose() {
		$db = connectToDB();
		
		$query = $db->prepare("UPDATE user SET closed = closed + 1 WHERE userID = :userID");
		
		$result = $query->execute(array(
			':userID'	=> $this->getUserId()));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
	}
	
	public function generateResetInfo(){
		mt_srand(time());
		//  0x1000000 = 16^6
		// 0x10000000 = 16^7
		$rand = mt_rand( 0x1000000,
		                0x10000000);
		$confirmCode = base_convert($rand, 10, 16);
		
		$db = connectToDB();

		$query = $db->prepare("
			UPDATE user
			SET resetConfirm = :resetConfirm,
			    resetTime = CURRENT_TIMESTAMP
			WHERE userID = :userID");
		
		$result = $query->execute(array(
			':resetConfirm'	=> $confirmCode,
			':userID'	=> $this->getUserId()));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		return $confirmCode;
	}
	
	public function verifyConfirmation($confirmCode){
		$db = connectToDB();

		$query = $db->prepare("SELECT resetConfirm, resetTime FROM user WHERE userID = :userID");
		
		$result = $query->execute(array(
			':userID'	=> $this->getUserId()));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$data = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();
		
		// If reset time does not exist (not sure how the DB returns NULLs)
		if($data === false || !isset($data['resetTime']) || !$data['resetTime'] || strcmp($data['resetTime'], "NULL") == 0){
			throw new UTRSIllegalModificationException("The confirmation code provided is not valid. Please fill " .
				"out the form below to request a password reset.");
		}
		$now = time();
		$then = strtotime($data['resetTime']);
		// 172800 seconds = 48 hours
		if($now - $then > 172800){
			throw new UTRSIllegalModificationException("The confirmation code provided has expired. Please fill" .
				" out the form below to request a password reset.");
		}
		if(strcmp($data['resetConfirm'], $confirmCode) != 0){
			throw new UTRSIllegalModificationException("The confirmation code provided is incorrect. Please fill " .
				"out the form below to request a password reset.");
		}
		
		return true;
	}
}

?>
