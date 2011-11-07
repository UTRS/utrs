<?php
ini_set('error_reporting', -1);
require_once('exceptions.php');

/**
 * This class contains information relevant to a single unblock appeal.
 * 
 */
class Appeal{
	
	/**
	 * The appeal is new and has not yet been addressed
	 */
	public static $STATUS_NEW = 'NEW';
	/**
	 * A response has been sent to the user, and a reply is expected
	 */
	public static $STATUS_AWAITING_USER = 'AWAITING_USER';
	/**
	 * The user has replied to a response, and the appeal is ready for further
	 * action from the handling administrator
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
	 * The appeal in question has been resolved
	 */
	public static $STATUS_CLOSED = 'CLOSED';
	
	/**
	 * Database ID number
	 */
	private $idNum;
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
	private $isAutoblock;
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
	 * Status of the appeal
	 */
	private $status;
	
	public function __construct(array $postVars){
		Appeal::validate($postVars); // may throw an exception
		
		$this->ipAddress = $_SERVER['REMOTE_ADDR'];
		$this->emailAddress = $postVars['email'];
		$this->hasAccount = (boolean) $postVars['registered'];
		$this->accountName = $postVars['accountName'];
		$this->isAutoBlock = (boolean) $postVars['autoBlock'];
		$this->blockingAdmin = $postVars['blockingAdmin'];
		$this->appeal = $postVars['appeal'];
		$this->intendedEdits = $postVars['intendedEdits'];
		$this->otherInfo = $postVars['otherInfo'];
		$this->timestamp = date('Y-m-d H:i:s');
		$this->handlingAdmin = null;
		$this->status = $STATUS_NEW;
		
		// TODO: insert into database
		
		// TODO: get database's assigned ID number
	}
	
	public static function validate(array $postVars){
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
		if(!isset($postVars["appeal"]) || $postVars["appeal"]){
			$errorMsgs .= "<br />You have not provided a reason why you wish to be unblocked.";
		}
		if(!isset($postVars["edits"]) || $postVars["edits"]){
			$errorMsgs .= "<br />You have not told us what edits you wish to make once unblocked.";
		}
		
		// validate fields
		if(!$emailErr && isset($postVars["email"])){
			$email = $postVars["email"];
			if(!Appeal::validEmail($email)){
				$errorMsgs .= "<br />You have not provided a valid email address.";
			}
		}
		
		// TODO: add queries to check if account exists or not
		
		if($errorMsgs){ // empty string is falsy
			throw new UTRSValidationException($errorMsgs);
		}
	}
	
	public function getID(){
		return $this->idNum;
	}
	
	public function getIP(){
		return $this->ipAddress;
	}
	
	public function getEmail(){
		return $this->emailAddress;
	}
	
	public function hasAccount(){
		return $this->hasAccount;
	}
	
	public function getAccountName(){
		return $this->accountName;
	}
	
	public function isAutoblock(){
		return $this->isAutoblock;
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
		return $this->handingAdmin;
	}
	
	public function getStatus(){
		return $this->status;
	}
	
	public function setStatus($newStatus){
		// TODO: query to check if status is closed; if so, whoever's reopening
		// should be a tool admin
		if(strcmp($newStatus, $STATUS_NEW) == 0 || strcmp($newStatus, $STATUS_AWAITING_USER) == 0
		  || strcmp($newStatus, $STATUS_AWAITING_ADMIN) == 0 || strcmp($newStatus, $STATUS_AWAITING_CHECKUSER) == 0
		  || strcmp($newStatus, $STATUS_AWAITING_PROXY) == 0 || strcmp($newStatus, $STATUS_CLOSED) == 0){
			// TODO: query to modify the row
			$this->status = $newStatus;
		}
		else{
			// Note: this shouldn't happen
			throw new UTRSIllegalModificationException("The status you provided is invalid.");
		}
	}
	
	public function setHandlingAdmin($admin){
		if($this->handlingAdmin != null){
			throw new UTRSIllegalModificationException("This request is already reserved. "
			  . "If the person holding this ticket seems to be unavailable, ask a tool "
			  . "admin to break their reservation.");
		}
		// TODO: Add a check to ensure that each person is only handling one 
		// at a time? Or allow multiple reservations?
		
		// TODO: query to modify the row
		
		$this->handlingAdmin = $admin;
	}
	
	/**
	 Validate an email address.
	 Provide email address (raw input)
	 Returns true if the email address has the email
	 address format and the domain exists.
	 
	 This function taken from http://www.linuxjournal.com/article/9585?page=0,3
	 as it's for linux and posted for anyone to use, I shall assume it's ok
	 with licensing and such.
	 */
	public static function validEmail($email)
	{
		$isValid = true;
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex)
		{
			$isValid = false;
		}
		else
		{
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64)
			{
				// local part length exceeded
				$isValid = false;
			}
			else if ($domainLen < 1 || $domainLen > 255)
			{
				// domain part length exceeded
				$isValid = false;
			}
			else if ($local[0] == '.' || $local[$localLen-1] == '.')
			{
				// local part starts or ends with '.'
				$isValid = false;
			}
			else if (preg_match('/\\.\\./', $local))
			{
				// local part has two consecutive dots
				$isValid = false;
			}
			else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
			{
				// character not valid in domain part
				$isValid = false;
			}
			else if (preg_match('/\\.\\./', $domain))
			{
				// domain part has two consecutive dots
				$isValid = false;
			}
			else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
			str_replace("\\\\","",$local)))
			{
				// character not valid in local part unless
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/',
				str_replace("\\\\","",$local)))
				{
					$isValid = false;
				}
			}
			if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
			{
				// domain not found in DNS
				$isValid = false;
			}
		}
		return $isValid;
	}
}

?>