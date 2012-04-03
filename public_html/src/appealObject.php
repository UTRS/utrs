<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('exceptions.php');
require_once('unblocklib.php');


/**
 * This class contains information relevant to a single unblock appeal.
 * 
 */
class Appeal{
	
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
	 * Email blacklist in regex form
	 */
	public static $EMAIL_BLACKLIST = '~@(wiki(p|m)edia|mailinator)~';
	
	/**
	 * Database ID number
	 */
	private $appealID;
	/**
	 * The IP address used to make the request; presumably the blocked one
	 * if the appealer doesn't have an account or it's an auto or rangeblock.
	 */
	private $ipAddress;
	/**
	 * The appealer's email address
	 */
	private $emailAddress;
	/**
	 * If the user already has an account
	 */
	private $hasAccount;
	/**
	 * The user's existing or desired account name
	 */
	private $accountName;
	/**
	 * If this is an auto- or range-block
	 */
	private $isAutoBlock;
	/**
	 * The blocking administrator
	 */
	private $blockingAdmin;
	/**
	 * The text of the appeal
	 */
	private $appeal;
	/**
	 * What edits the user intends to make if unblocked
	 */
	private $intendedEdits;
	/**
	 * Other information
	 */
	private $otherInfo;
	/**
	 * Time the request was placed
	 */
	private $timestamp;
	/**
	 * The admin handling the appeal
	 */
	private $handlingAdmin;
	/**
	 * The old admin handling the appeal
	 */
	private $oldHandlingAdmin;
	/**
	 * Last log action
	 */
	private $lastLogId;
	/**
	 * Status of the appeal
	 */
	private $status;
	
	/**
	 * User agent
	 */
	private $useragent;
	
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
	public function __construct(array $values, $fromDB){
		debug('In constuctor for Appeal <br/>');
		if(!$fromDB){
			debug('Obtaining values from form <br/>');
			Appeal::validate($values); // may throw an exception
		
			$this->ipAddress = Appeal::getIPFromServer();
			$this->emailAddress = sanitizeText($values['email']);
			$this->hasAccount = (boolean) $values['registered'];
			$this->accountName = sanitizeText($values['accountName']);
			$this->isAutoBlock = (boolean) (isset($values['autoBlock']) ? $values['autoBlock'] : false);
			$this->blockingAdmin = sanitizeText($values['blockingAdmin']);
			$this->appeal = sanitizeText($values['appeal']);
			$this->intendedEdits = sanitizeText($values['edits']);
			$this->otherInfo = sanitizeText($values['otherInfo']);
			$this->handlingAdmin = null;
			$this->oldHandlingAdmin = null;
			$this->status = Appeal::$STATUS_NEW;
			
			debug('Setting values complete <br/>');
			
			Appeal::insert();
		}
		else{
			debug('Obtaining values from DB <br/>');
			$this->appealID = $values['appealID'];
			$this->ipAddress = $values['ip'];
			$this->emailAddress = $values['email'];
			$this->hasAccount = $values['hasAccount'];
			$this->accountName = $values['wikiAccountName'];
			$this->isAutoBlock = $values['autoblock'];
			$this->blockingAdmin = $values['blockingAdmin'];
			$this->appeal = $values['appealText'];
			$this->intendedEdits = $values['intendedEdits'];
			$this->otherInfo = $values['otherInfo'];
			$this->timestamp = $values['timestamp'];
			if ($values['handlingAdmin']) {
				$this->handlingAdmin = User::getUserById($values['handlingAdmin']);
			} else {
				$this->handlingAdmin = null;
			}
			$this->oldHandlingAdmin = $values['oldHandlingAdmin'];
			$this->status = $values['status'];
			$this->useragent = Appeal::getCheckUserData($this->appealID);
		}
		debug('Exiting constuctor <br/>');
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
		
		$query = 'SELECT * FROM appeal ';
		$query .= 'WHERE appealID = \'' . $id . '\'';
		
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
		
		return new Appeal($values, true);
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
		if ($this->handlingAdmin != null) {
			$query .= "handlingAdmin = " . $this->handlingAdmin->getUserId() . ", ";
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
	
	public static function validate(array $postVars){
		debug('Entering validate for Appeal <br/>');
		$errorMsgs = "";
		$hasAccount = false;
		$emailErr = false;
		
		// confirm that all required fields exist
		if(!isset($postVars["email"]) || strcmp(trim($postVars["email"]), '') == 0 ){
			$emailErr = true;
			$errorMsgs .= "<br />An email address is required in order to stay in touch with you about your appeal.";
		}
		if(!isset($postVars["registered"])){
			$errorMsgs .= "<br />We need to know if you have an account on the English Wikipedia.";
		}
		else{
			$hasAccount = $postVars["registered"];
		}
		if($hasAccount && (!isset($postVars["accountName"]) || strcmp(trim($postVars["accountName"]), '') == 0 )){
			$errorMsgs .= "<br />If you have an account, we need to know the name of your account.";
		}
		if($hasAccount && !isset($postVars["autoBlock"])){
			$errorMsgs .= "<br />If you have an account, we need to know if you are appealing a direct block or an IP block.";
		}
		if(!isset($postVars["blockingAdmin"]) || strcmp(trim($postVars["blockingAdmin"]), '') == 0){
			$errorMsgs .= "<br />We need to know which administrator placed your block.";
		}
		if(!isset($postVars["appeal"]) || strcmp(trim(sanitizeText($postVars["appeal"])), '') == 0){
			$errorMsgs .= "<br />You have not provided a reason why you wish to be unblocked.";
		}
		if(!isset($postVars["edits"]) || strcmp(trim(sanitizeText($postVars["edits"])), '') == 0){
			$errorMsgs .= "<br />You have not told us what edits you wish to make once unblocked.";
		}
		
		// validate fields
		if(!$emailErr && isset($postVars["email"])){
			$email = $postVars["email"];
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
		if(strpos($postVars["accountName"], "#") !== false | strpos($postVars["accountName"], "/") !== false |
		   strpos($postVars["accountName"], "|") !== false | strpos($postVars["accountName"], "[") !== false |
		   strpos($postVars["accountName"], "]") !== false | strpos($postVars["accountName"], "{") !== false |
		   strpos($postVars["accountName"], "}") !== false | strpos($postVars["accountName"], "<") !== false |
		   strpos($postVars["accountName"], ">") !== false | strpos($postVars["accountName"], "@") !== false |
		   strpos($postVars["accountName"], "%") !== false | strpos($postVars["accountName"], ":") !== false | 
		   strpos($postVars["accountName"], '$') !== false){
		   	$errorMsgs .= 'The username you have entered is invalid. Usernames ' .
		   	 	'may not contain the characters # / | [ ] { } < > @ % : $';
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
		return $this->handlingAdmin;
	}
	
	public function getOldHandlingAdmin(){
		return $this->oldHandlingAdmin;
	}
	
	public function getStatus(){
		return $this->status;
	}
	
	public function getUserAgent() {
		return $this->useragent;
	}
	
	public static function getAppealCountByIP($ip) {
		
		$db = connectToDB();
		
		$query = "SELECT COUNT(*) as count FROM appeal WHERE ip = '" . $ip . "' OR ip = '" . md5($ip) . "';";
		debug($query);
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			return 0;
		} else {
			$data = mysql_fetch_array($result);
			return $data['count'];
		}
	}
	
	public function setStatus($newStatus){
		// TODO: query to check if status is closed; if so, whoever's reopening
		// should be a tool admin
		if(strcmp($newStatus, $this::$STATUS_NEW) == 0 || strcmp($newStatus, $this::$STATUS_AWAITING_USER) == 0
		  || strcmp($newStatus, $this::$STATUS_AWAITING_ADMIN) == 0 || strcmp($newStatus, $this::$STATUS_AWAITING_CHECKUSER) == 0
		  || strcmp($newStatus, $this::$STATUS_AWAITING_PROXY) == 0 || strcmp($newStatus, $this::$STATUS_CLOSED) == 0
		  || strcmp($newStatus, $this::$STATUS_ON_HOLD) == 0 || strcmp($newStatus, $this::$STATUS_AWAITING_REVIEWER) == 0){
			// TODO: query to modify the row
			$this->status = $newStatus;
			if ($this->status == $this::$STATUS_CLOSED) {
				User::getUserByUsername($_SESSION['user'])->incrementClose();
			}
		}
		else{
			// Note: this shouldn't happen
			throw new UTRSIllegalModificationException("The status you provided is invalid.");
		}
	}
	
	public function setHandlingAdmin($admin, $saveadmin = 0){
		if($this->handlingAdmin != null && $admin != null){
			throw new UTRSIllegalModificationException("This request is already reserved. "
			  . "If the person holding this ticket seems to be unavailable, ask a tool "
			  . "admin to break their reservation.");
		}
		
		if ($this->handlingAdmin == null && $admin == null) {
			return false;
		}
		// TODO: Add a check to ensure that each person is only handling one 
		// at a time? Or allow multiple reservations?
		
		// TODO: query to modify the row
		if ($saveadmin == 1 && $this->handlingAdmin != NULL) {
			$this->oldHandlingAdmin = $this->handlingAdmin->getUserId();
		}
		if ($admin != null) {
				$this->handlingAdmin = User::getUserById($admin);
		} else {
				$this->handlingAdmin = null;
		}
		return true;
	}
	
	public function returnHandlingAdmin() {
		 if ($this->oldHandlingAdmin != NULL) {
		 	$this->handlingAdmin = User::getUserById($this->oldHandlingAdmin);
		 	$this->oldHandlingAdmin = null;
		 }
	}
	
	public function getLastLogId() {
		return $this->lastLogId;
	}
	
	public function updateLastLogId($log_id) {
		
		$db = ConnectToDB();
		
		$query = "UPDATE appeal SET lastLogId = " . $log_id . " WHERE appealID = " . $this->appealID . ";";
		echo $query;
		mysql_query($query, $db);
		
		$this->lastLogId = $log_id;
	}
}

?>