<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('model.php');
require_once('exceptions.php');
require_once('unblocklib.php');


/**
 * This class contains information relevant to a single unblock appeal.
 * 
 */
class Appeal extends Model {
	
	/**
	 * IP addresses belonging to the Toolserver that may get in the way
	 * of identifying the requestor's IP
	 */
	public static $TOOLSERVER_IPS = '91.198.174.197,91.198.174.204';
	/**
	 * The appeal is new and has not yet been addressed
	 */
	public static $STATUS_NEW = 'NEW';
	/**
	 * A response has been sent to the user, and a reply is expected
	 */
	public static $STATUS_AWAITING_USER = 'AWAITING_USER';
	/**
	 * The appeal needs to be reviewed by a tool admin
	 */
	public static $STATUS_AWAITING_ADMIN = 'AWAITING_ADMIN';
	/**
	 * The appeal needs to be reviewed by a checkuser before it can proceed
	 */
	public static $STATUS_AWAITING_CHECKUSER = 'AWAITING_CHECKUSER';
	/**
	 * The appeal needs to be reviewed by OPP before it can proceed
	 */
	public static $STATUS_AWAITING_PROXY = 'AWAITING_PROXY';
	/**
	 * The user has replied to a response, and the appeal is ready for further
	 * action from the handling administrator
	 */
	public static $STATUS_AWAITING_REVIEWER = 'AWAITING_REVIEWER';
	/**
	 * The appeal is on hold
	 */
	public static $STATUS_ON_HOLD = 'ON_HOLD';
	/**
	 * The appeal in question has been resolved
	 */
	public static $STATUS_CLOSED = 'CLOSED';
	/**
	 * the requester has been emailed with a reminder of his appeal
	 */
	public static $STATUS_REMINDED = 'REMINDED';
	/**
	 * Email blacklist in regex form
	 */
	public static $EMAIL_BLACKLIST = '~@(wiki(p|m)edia|mailinator)~';
	
	/**
	 * Database ID number
	 */
	protected $appealID;
	/**
	 * The IP address used to make the request; presumably the blocked one
	 * if the appealer doesn't have an account or it's an auto or rangeblock.
	 */
	protected $ipAddress;
	/**
	 * The appealer's email address
	 */
	protected $emailAddress;
	/**
	 * If the user already has an account
	 */
	protected $hasAccount;
	/**
	 * The user's existing or desired account name
	 */
	protected $accountName;
	/**
	 * If this is an auto- or range-block
	 */
	protected $isAutoBlock;
	/**
	 * The blocking administrator
	 */
	protected $blockingAdmin;
	/**
	 * The text of the appeal
	 */
	protected $appeal;
	/**
	 * What edits the user intends to make if unblocked
	 */
	protected $intendedEdits;
	/**
	 * Other information
	 */
	protected $otherInfo;
	/**
	 * Time the request was placed
	 */
	protected $timestamp;
	/**
	 * The admin handling the appeal
	 */
	protected $handlingAdmin;
	protected $handlingAdminObject;
	/**
	 * The old admin handling the appeal
	 */
	protected $oldHandlingAdmin;
	/**
	 * Last log action
	 */
	protected $lastLogId;
	/**
	 * Status of the appeal
	 */
	protected $status;
	
	/**
	 * User agent
	 */
	protected $useragent;

	// Maps from DB columns to object fields.
	private static $columnMap = array(
		'appealID'		=> 'appealID',
		'email'			=> 'emailAddress',
		'ip'			=> 'ipAddress',
		'wikiAccountName'	=> 'accountName',
		'autoblock'		=> 'isAutoBlock',
		'hasAccount'		=> 'hasAccount',
		'blockingAdmin'		=> 'blockingAdmin',
		'timestamp'		=> 'timestamp',
		'appealText'		=> 'appeal',
		'intendedEdits'		=> 'intendedEdits',
		'otherInfo'		=> 'otherInfo',
		'status'		=> 'status',
		'handlingAdmin'		=> 'handlingAdmin',
		'oldHandlingAdmin'	=> 'oldHandlingAdmin',
		'lastLogId'		=> 'lastLogId');

	private static $badAccountCharacters = '# / | [ ] { } < > @ % : $';

	public static function getColumnMap() {
		return self::$columnMap;
	}

	public static function getColumnsForSelect($table_alias = 'appeal') {
		return parent::getColumnsForSelect(array_keys(self::$columnMap), 'appeal_', $table_alias);
	}
	
	/**
	 * Build a Appeal object. If $fromDB is true, the mappings in $values
	 * will be assumed to be those from the database; additionally,
	 * the values will not be validated and the object will not be inserted
	 * the DB. Otherwise, the mappings in $values will be assumed to be those
	 * from the appeals form; values will be validated, and the object
	 * will be inserted into the DB on completion.
	 * 
	 * @param array $values the information to include in this appeal
	 * @param boolean $fromDB is this from the database?
	 */
	public function __construct($values = false){
		debug('In constuctor for Appeal <br/>');

		// Set defaults
		$this->ipAddress = self::getIPFromServer();
		$this->status = self::$STATUS_NEW;
		$this->handlingAdmin = null;
		$this->handlingAdminObject = null;

		// False means "uncached", getUserAgent() will fetch it when
		// called, if the user has permission.
		$this->useragent = false;

		if (is_array($values)) {
			$this->populate($values);
		}

		debug('Exiting constuctor <br/>');
	}

	public function populate($map) {
		$this->populateFromMap(self::$columnMap, 'appeal_', $map);
	}
	
	public static function getIPFromServer(){
		$ip = $_SERVER["REMOTE_ADDR"]; // default address
		
		// if the IP is one of the toolserver's IPs...
		if(!(strpos(Appeal::$TOOLSERVER_IPS, $ip) === false)){
			// code in this if block stolen from ACC - thanks, guys
			$xffheader = explode(",", getenv("HTTP_X_FORWARDED_FOR"));
			$sourceip = trim($xffheader[sizeof($xffheader)-1]);
			if (preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $sourceip)) {
				// $proxyip = $ip; // ignoring proxy IP for now
				$ip = $sourceip;
			}
		}
		
		return $ip;
	}
	
	public static function getAppealByID($id){
		$db = connectToDB();
		
		$query = "
			SELECT " . self::getColumnsForSelect() . " FROM appeal
			WHERE appealID = '" . (int)$id . "'";
		
		$result = mysql_query($query, $db);
		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}
		if(mysql_num_rows($result) == 0){
			throw new UTRSDatabaseException('No results were returned for appeal ID ' . $id);
		}
		if(mysql_num_rows($result) != 1){
			throw new UTRSDatabaseException('Please contact a tool developer. More '
				. 'than one result was returned for appeal ID ' . $id);
		}
		
		$values = mysql_fetch_assoc($result);
		
		return new Appeal($values);
	}
	
	public static function getCheckUserData($appealID) {
		if (verifyAccess($GLOBALS['CHECKUSER']) || verifyAccess($GLOBALS['ADMIN'])) {
			$db = connectToDB();
			
			$query = "SELECT useragent FROM cuData WHERE appealID = " . $appealID . ";";
			
			$result = mysql_query($query, $db);
			if(!$result){
				$error = mysql_error($db);
				throw new UTRSDatabaseException($error);
			}
			if(mysql_num_rows($result) == 0){
				return null;
			}
			if(mysql_num_rows($result) != 1){
				throw new UTRSDatabaseException('Please contact a tool developer. More '
				. 'than one result was returned for appeal ID ' . $appealID);
			}
			
			$values = mysql_fetch_assoc($result);
			
			return $values['useragent'];
		} else {
			return null;
		}
		
	}
	
	public function insert(){
		$this->validate();

		debug('In insert for Appeal <br/>');
		
		$db = connectToDB();
		
		debug('Database connected <br/>');
		
		$query = 'INSERT INTO appeal (email, ip, ';
		$query .= ($this->accountName ? 'wikiAccountName, ' : '');
		$query .= 'autoblock, hasAccount, blockingAdmin, appealText, ';
		$query .= 'intendedEdits, otherInfo, status) VALUES (';
		$query .= '\'' . mysql_real_escape_string($this->emailAddress) . '\', ';
		$query .= '\'' . $this->ipAddress . '\', ';
		$query .= ($this->accountName ? '\'' . mysql_real_escape_string($this->accountName, $db) . '\', ' : '');
		$query .= ($this->isAutoBlock ? '\'1\', ' : '\'0\', ');
		$query .= ($this->hasAccount ? '\'1\', ' : '\'0\', ');
		$query .= '\'' . mysql_real_escape_string($this->blockingAdmin, $db) . '\', ';
		$query .= '\'' . mysql_real_escape_string($this->appeal, $db) . '\', ';
		$query .= '\'' . mysql_real_escape_string($this->intendedEdits, $db) . '\', ';
		$query .= '\'' . mysql_real_escape_string($this->otherInfo, $db) . '\', ';
		$query .= '\'' . $this->status . '\')';
		
		debug($query . ' <br/>');
		
		$result = mysql_query($query, $db);
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		debug('Insert complete <br/>');
		
		$this->appealID = mysql_insert_id($db);
		
		debug('Getting timestamp <br/>');
		
		$query = 'SELECT timestamp FROM appeal WHERE appealID = \'' . $this->appealID . '\'';
		$result = mysql_query($query, $db);
		$row = mysql_fetch_assoc($result);
		
		$this->timestamp = $row["timestamp"];
		
		debug('Primary insert complete. Beginning useragent retrieval.<br/>');
		
		$query = 'INSERT INTO cuData (appealID, useragent) VALUES (\'';
		$query .= $this->appealID;
		$query .= '\', \'';
		$query .= $_SERVER['HTTP_USER_AGENT'];
		$query .= '\')';
		
		$this->useragent = sanitizeText($_SERVER['HTTP_USER_AGENT']);
		
		$result = mysql_query($query, $db);
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		debug('Exiting insert <br/>');
	}
	
	public function update() {
		debug('In update function');
		
		$db = connectToDB();
		
		debug('connected to database');
		
		$query = "UPDATE appeal SET ";
		if ($this->getHandlingAdminId() != null) {
			$query .= "handlingAdmin = " . $this->getHandlingAdminId() . ", ";
		} else {
			$query .= "handlingAdmin = null, ";
		}
		if ($this->oldHandlingAdmin != null) {
			$query .= "oldHandlingAdmin = " . $this->oldHandlingAdmin . ", ";
		} else {
			$query .= "oldHandlingAdmin = null, ";
		}
		$query .= "status = '" . $this->status . "' ";
		$query .= "WHERE appealID = " . $this->appealID . ";";
		
		debug($query);
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		debug("Update complete");
	
	}
	
	public function validate(){
		debug('Entering validate for Appeal <br/>');
		$errorMsgs = "";
		$emailErr = false;
		
		// confirm that all required fields exist
		if(!isset($this->emailAddress) || strcmp(trim($this->emailAddress), '') == 0 ){
			$emailErr = true;
			$errorMsgs .= "<br />An email address is required in order to stay in touch with you about your appeal.";
		}
		if(!isset($this->hasAccount)){
			$errorMsgs .= "<br />We need to know if you have an account on the English Wikipedia.";
		}
		if($this->hasAccount){
			if(!isset($this->accountName) || strcmp(trim($this->accountName), '') == 0 ){
				$errorMsgs .= "<br />If you have an account, we need to know the name of your account.";
			}
			if(!isset($this->isAutoBlock)){
				$errorMsgs .= "<br />If you have an account, we need to know if you are appealing a direct block or an IP block.";
			}
		}
		if(!isset($this->blockingAdmin) || strcmp(trim($this->blockingAdmin), '') == 0){
			$errorMsgs .= "<br />We need to know which administrator placed your block.";
		}
		if(!isset($this->appeal) || strcmp(trim($this->appeal), '') == 0){
			$errorMsgs .= "<br />You have not provided a reason why you wish to be unblocked.";
		}
		if(!isset($this->intendedEdits) || strcmp(trim($this->intendedEdits), '') == 0){
			$errorMsgs .= "<br />You have not told us what edits you wish to make once unblocked.";
		}
		
		// validate fields
		if(!$emailErr){
			$email = $this->emailAddress;
			if(!validEmail($email)){
				$errorMsgs .= "<br />You have not provided a valid email address.";
			}
			$matches = array();
			if(preg_match(Appeal::$EMAIL_BLACKLIST, $email, $matches)){
				if($matches[1] == "mailinator"){
					$errorMsgs .= "<br />Temporary email addresses, such as those issued by Mailinator, are not accepted.";
				}
				else{
					$errorMsgs .= "<br />The email address you have entered is blacklisted. You must enter an email address that you own.";
				}
			}
		}

		if (isset($this->accountName)) {
			foreach (explode(' ', self::$badAccountCharacters) as $c) {
				if (strpos($this->accountName, $c) !== false) {
					$errorMsgs .= '<br />The username you have entered is invalid. Usernames ' .
						'may not contain the characters ' . self::$badAccountCharacters;
					break;
				}
			}
		}
		
		// TODO: add queries to check if account exists or not
		
		if($errorMsgs){ // empty string is falsy
			debug('Validation errors: ' . $errorMsgs . ' <br/>');
			throw new UTRSValidationException($errorMsgs);
		}
		
		debug('Exiting Appeal <br/>');
	}
	
	public function getID(){
		return $this->appealID;
	}
	
	public function getIP(){
		return $this->ipAddress;
	}
	
	public function getAccountName(){
		return $this->accountName;
	}
	
	public function getCommonName() {
		if ($this->accountName  && $this->hasAccount) {
			return $this->accountName;
		} else {
			return $this->ipAddress;
		}
	}
	
	public function getUserPage() {
		if ($this->accountName && $this->hasAccount) {
			return "User:" . $this->accountName;
		} else {
			return "Special:Contributions/" . $this->ipAddress;
		}
	}
	
	public function getEmail(){
		return $this->emailAddress;
	}
	
	public function hasAccount(){
		return $this->hasAccount;
	}
	
	public function isAutoblock(){
		return $this->isAutoBlock;
	}
	
	public function getBlockingAdmin(){
		return $this->blockingAdmin;
	}
	
	public function getAppeal(){
		return $this->appeal;
	}
	
	public function getIntendedEdits(){
		return $this->intendedEdits;
	}
	
	public function getOtherInfo(){
		return $this->otherInfo;
	}
	
	public function getTimestamp(){
		return $this->timestamp;
	}
	
	public function getHandlingAdmin(){
		if (!is_null($this->handlingAdminObject)) {
			return $this->handlingAdminObject;
		}

		if (!is_null($this->handlingAdmin)) {
			$this->handlingAdminObject = User::getUserById($this->handlingAdmin);
			return $this->handlingAdminObject;
		}

		return null;
	}

	public function getHandlingAdminId() {
		if (!is_null($this->handlingAdminObject)) {
			return $this->handlingAdminObject->getUserId();
		}

		if (!is_null($this->handlingAdmin)) {
			return $this->handlingAdmin;
		}

		return null;
	}
	
	public function getOldHandlingAdmin(){
		return $this->oldHandlingAdmin;
	}
	
	public function getStatus(){
		return $this->status;
	}
	
	public function getUserAgent() {
		if ($this->useragent !== false) {
			return $this->useragent;
		}

		$this->useragent = $this->getCheckUserData($this->appealID);
		return $this->useragent;
	}
	
	public static function getAppealCountByIP($ip) {
		
		$db = connectToDB();
		
		$query = "SELECT COUNT(*) as count FROM appeal WHERE ip = '" . $ip . "' OR ip = '" . md5($ip) . "' OR MD5(ip) = '" . $ip . "';";
		debug($query);
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			return 0;
		} else {
			$data = mysql_fetch_array($result);
			return $data['count'];
		}
	}
	
	public function setStatus($newStatus, $username = false){
		if ($username === false){
			$username = $_SESSION['user'];
		}
		// TODO: query to check if status is closed; if so, whoever's reopening
		// should be a tool admin
		
		if(strcmp($newStatus, self::$STATUS_NEW) == 0 || strcmp($newStatus, self::$STATUS_AWAITING_USER) == 0
		  || strcmp($newStatus, self::$STATUS_AWAITING_ADMIN) == 0 || strcmp($newStatus, self::$STATUS_AWAITING_CHECKUSER) == 0
		  || strcmp($newStatus, self::$STATUS_AWAITING_PROXY) == 0 || strcmp($newStatus, self::$STATUS_CLOSED) == 0
		  || strcmp($newStatus, self::$STATUS_ON_HOLD) == 0 || strcmp($newStatus, self::$STATUS_AWAITING_REVIEWER) == 0
		  || strcmp($newStatus, self::$STATUS_REMINDED) == 0){
			// TODO: query to modify the row
			$this->status = $newStatus;
			if ($this->status == self::$STATUS_CLOSED) {
				User::getUserByUsername($username)->incrementClose();
			}
		}
		else{
			// Note: this shouldn't happen
			throw new UTRSIllegalModificationException("The status you provided is invalid.");
		}
	}
	
	public function setHandlingAdmin($admin, $saveadmin = 0){
		if($this->getHandlingAdminId() != null && $admin != null){
			throw new UTRSIllegalModificationException("This request is already reserved. "
			  . "If the person holding this ticket seems to be unavailable, ask a tool "
			  . "admin to break their reservation.");
		}
		
		if ($this->getHandlingAdminId() == null && $admin == null) {
			return false;
		}
		// TODO: Add a check to ensure that each person is only handling one 
		// at a time? Or allow multiple reservations?
		
		// TODO: query to modify the row
		if ($saveadmin == 1 && $this->getHandlingAdminId() != NULL) {
			$this->oldHandlingAdmin = $this->getHandlingAdminId();
		}

		$this->handlingAdmin = $admin;
		$this->handlingAdminObject = null;	// Invalidate cache

		return true;
	}
	
	public function returnHandlingAdmin() {
		 if ($this->oldHandlingAdmin != NULL) {
		 	$this->handlingAdmin = $this->oldHandlingAdmin;
			$this->handlingAdminObject = null;

		 	$this->oldHandlingAdmin = null;
		 }
	}
	
	public function getLastLogId() {
		return $this->lastLogId;
	}
	
	public function updateLastLogId($log_id) {
		
		$db = ConnectToDB();
		
		$query = "UPDATE appeal SET lastLogId = " . $log_id . " WHERE appealID = " . $this->appealID . ";";
		
		debug($query);
		
		mysql_query($query, $db);
		
		$this->lastLogId = $log_id;
	}
	
	public function sendEmail($bodytemplate, $subject, $admin = false){
		$success = false;
		try {
			if ($admin === false){
				$admin = getCurrentUser();
			}
			$headers = "From: Unblock Review Team <noreply-unblock@toolserver.org>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$et = new EmailTemplates($admin, $this);
			$body = $et->apply_to($bodytemplate);
			mail($this->emailAddress, $subject, $body, $headers);
			$success = true;
			//Put the contents of the email into the log
			$log = Log::getCommentsByAppealId($this->getID());
			$log->addNewItem($et->censor_email($et->apply_to($bodytemplate)), 0, $admin->getUsername());
			
		} catch (Exception $e) {
			//log email failure
			$log = Log::getCommentsByAppealId($this->getID());
			$log->addNewItem("failed to send email", 0 , $admin->getUsername());
			$errors = $e->getMessage();
		} 
		return $success;
	}
}

?>
