<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('model.php');
require_once('exceptions.php');
require_once('unblocklib.php');
require_once('UTRSBot.class.php');
require_once("includes/Peachy/Init.php");
require_once('messages.php');


/**
 * This class contains information relevant to a single unblock appeal.
 * 
 */
class Appeal extends Model {
   
   /**
    * IP addresses belonging to the Toolserver that may get in the way
    * of identifying the requestor's IP
    */
   public static $TOOLSERVER_IPS = '91.198.174.197,91.198.174.204,185.15.59.204,185.15.59.197,10.4.1.89,tools.wmflabs.org,10.4.0.78,10.68.16.65,dynamicproxy-gateway.eqiad.wmflabs,10.68.21.68,novaproxy-01.project-proxy.eqiad.wmflabs';
   /**
    * The appeal has not yet passed email verification
    */
   public static $STATUS_UNVERIFIED = 'UNVERIFIED';
   /**
    * The appeal has been marked invalid by a developer
    */
   public static $STATUS_INVALID = 'INVALID';
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
   * Why the user thinks they are blocked
   */
   protected $blockReason;
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
   protected $useragent = false;

   /**
    * Email verification token
    */
   protected $emailToken;

   // Maps from DB columns to object fields.
   private static $columnMap = array(
      'appealID'     => 'appealID',
      'email'        => 'emailAddress',
      'ip'        => 'ipAddress',
      'wikiAccountName' => 'accountName',
      'autoblock'    => 'isAutoBlock',
      'hasAccount'      => 'hasAccount',
      'blockingAdmin'      => 'blockingAdmin',
      'timestamp'    => 'timestamp',
      'appealText'      => 'appeal',
      'intendedEdits'      => 'intendedEdits',
      'blockReason'     => 'blockReason',
      'otherInfo'    => 'otherInfo',
      'status'    => 'status',
      'handlingAdmin'      => 'handlingAdmin',
      'oldHandlingAdmin'   => 'oldHandlingAdmin',
      'lastLogId'    => 'lastLogId',
      'emailToken'      => 'emailToken');

   private static $badAccountCharacters = '# / | [ ] { } < > @ % : $';
     
   private static $config = "UTRSBot";

   public static function getColumnMap() {
      return self::$columnMap;
   }

   public static function getColumnsForSelect($table_alias = 'appeal') {
      return parent::getColumnsForSelectBase(array_keys(self::$columnMap), 'appeal_', $table_alias);
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
   private function __construct($values = false){
      debug('In constuctor for Appeal <br/>');

      if (is_array($values)) {
         $this->populate($values);
      }

      debug('Exiting constuctor <br/>');
   }

   public static function newUntrusted($values) {
	   
      $objPeachy = Peachy::newWiki( self::$config );
	  
      $appeal = new Appeal($values);

      $appeal->ipAddress = self::getIPFromServer();
      $appeal->status = self::$STATUS_UNVERIFIED;
      $appeal->handlingAdmin = null;
      $appeal->handlingAdminObject = null;
	  
	  //Get blocking admin from API
	  // WARNING: These need to be the raw values to get the appropriate block information from
	  // the API. DO NOT CHANGE.
      if ($appeal->isAutoBlock()) {
	  	$blockinfo = $objPeachy->initUser( $appeal->getIP() )->get_blockinfo();
      }
      else {
      	$blockinfo = $objPeachy->initUser( $appeal->getCommonName() )->get_blockinfo();
      }
	  $appeal->blockingAdmin = $blockinfo['by'];
	  

      // False means "uncached", getUserAgent() will fetch it when
      // called, if the user has permission.
      $appeal->useragent = false;

      // Generate email token
      $token = '';
      for ($i = 0; $i < 32; $i++) {
         $token .= sprintf("%02x", mt_rand(0, 255));
      }

      $appeal->emailToken = $token;

      return $appeal;
   }

   public static function newTrusted($values) {
      return new Appeal($values);
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
      
      $query = $db->prepare("
         SELECT " . self::getColumnsForSelect() . " FROM appeal
         WHERE appealID = :appealID");

      $result = $query->execute(array(
         ':appealID' => $id));
      
      if(!$result){
         $error = var_export($query->errorInfo(), true);
         throw new UTRSDatabaseException($error);
      }

      $values = $query->fetch(PDO::FETCH_ASSOC);
      $query->closeCursor();

      if ($values === false) {
         throw new UTRSDatabaseException(SystemMessages::$error['NoResults'][$lang].' ' . $id);
      }
      
      return self::newTrusted($values);
   }
   public function checkRevealLog($userID,$item) {
   	$appealID = $this->appealID;
   	$db = connectToDB();
   	
   	$query = $db->prepare("
         SELECT revealID FROM revealFlags
         WHERE appealID = :appealID AND item = :item AND toUser = :touser");
   	
   	$result = $query->execute(array(
   			':appealID' => $appealID,':item' => $item, ':touser' => $userID));
   	
   	if(!$result){
   		$error = var_export($query->errorInfo(), true);
   		throw new UTRSDatabaseException($error);
   	}
   	
   	$values = $query->fetch(PDO::FETCH_ASSOC);
   	$query->closeCursor();
   	
   	if ($values === false) {
   		return False;
   	}
   	
   	return True;
   }
   public function insertRevealLog($userID,$item) {
   	$appealID = $this->appealID;
   	$db = connectToDB();
   
   	$query = $db->prepare("
         INSERT INTO revealFlags (appealID, item, toUser) VALUES (:appealID, :item, :toUser)");
   
   	$result = $query->execute(array(
   			':appealID' => $appealID,':item' => $item, ':toUser' => $userID));
   
   	if(!$result){
   		$error = var_export($query->errorInfo(), true);
   		throw new UTRSDatabaseException($error);
   	}
   
   	return;
   }
   public static function getCheckUserData($appealID) {
   	  //Check reveal
      if (verifyAccess($GLOBALS['CHECKUSER']) || verifyAccess($GLOBALS['DEVELOPER'])) {
         $db = connectToDB();
         
         $query = $db->prepare("SELECT useragent FROM cuData WHERE appealID = :appealID");
         
         $result = $query->execute(array(
            ':appealID' => $appealID));
         
         if(!$result){
            $error = var_export($query->errorInfo(), true);
            throw new UTRSDatabaseException($error);
         }

         $values = $query->fetch(PDO::FETCH_ASSOC);
         $query->closeCursor();

         if ($values !== false) {
            return $values['useragent'];
         }
      }

      return null;
   }
   
   public function insert(){
      $this->validate();

      debug('In insert for Appeal <br/>');
      
      $db = connectToDB();
      
      debug('Database connected <br/>');
      
      $query = $db->prepare("
         INSERT INTO appeal
         (email, ip, wikiAccountName, autoblock, hasAccount,
            blockingAdmin, appealText, intendedEdits,
            blockReason, otherInfo, status, emailToken)
         VALUES
         (:email, :ip, :wikiAccountName, :autoblock, :hasAccount,
            :blockingAdmin, :appealText, :intendedEdits,
            :blockReason, :otherInfo, :status, :emailToken)");

      $result = $query->execute(array(
         ':email'    => $this->emailAddress,
         ':ip'       => $this->ipAddress,
         ':wikiAccountName'   => $this->accountName,
         ':autoblock'      => (bool)$this->isAutoBlock,
         ':hasAccount'     => (bool)$this->hasAccount,
         ':blockingAdmin'  => $this->blockingAdmin,
         ':appealText'     => $this->appeal,
         ':intendedEdits'  => $this->intendedEdits,
         ':blockReason' => $this->blockReason,
         ':otherInfo'      => $this->otherInfo,
         ':status'      => $this->status,
         ':emailToken'     => $this->emailToken));

      if(!$result){
         $error = var_export($query->errorInfo(), true);
         debug('ERROR: ' . $error . '<br/>');
         throw new UTRSDatabaseException($error);
      }
      
      debug('Insert complete <br/>');
      
      $this->appealID = $db->lastInsertId();
      
      debug('Getting timestamp <br/>');
      
      $query = $db->prepare('SELECT timestamp FROM appeal WHERE appealID = :appealID');
      
      $result = $query->execute(array(
         ':appealID' => $this->appealID));

      $row = $query->fetch(PDO::FETCH_ASSOC);
      $query->closeCursor();

      $this->timestamp = $row["timestamp"];
      
      debug('Primary insert complete. Beginning useragent retrieval.<br/>');

      $query = $db->prepare('
         INSERT INTO cuData
         (appealID, useragent)
         VALUES (:appealID, :useragent)');

      //Prepare cuData string
      $cuData = gethostbyaddr($this->getIP()) . "<br />";

      if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $this->getIP() != $_SERVER['HTTP_X_FORWARDED_FOR'])  {
         $cuData .= $_SERVER['HTTP_X_FORWARDED_FOR'] . " " . gethostbyaddr($_SERVER['HTTP_X_FORWARDED_FOR']) . "<br />";
      }

      $cuData .=  $_SERVER['HTTP_USER_AGENT'];

      $result = $query->execute(array(
         ':appealID' => $this->appealID,
         ':useragent'   => $cuData));
      
      if(!$result){
         $error = var_export($query->errorInfo(), true);
         debug('ERROR: ' . $error . '<br/>');
         throw new UTRSDatabaseException($error);
      }
      
      debug('Exiting insert <br/>');
   }
   
   public function update() {
      debug('In update function');
      
      $db = connectToDB();
      
      debug('connected to database');

      $query = $db->prepare('
         UPDATE appeal
         SET handlingAdmin = :handlingAdmin,
             oldHandlingAdmin = :oldHandlingAdmin,
             status = :status
         WHERE appealID = :appealID');

      $result = $query->execute(array(
         ':handlingAdmin'  => $this->getHandlingAdminId(),
         ':oldHandlingAdmin'  => $this->oldHandlingAdmin,
         ':status'      => $this->status,
         ':appealID'    => $this->appealID));
      
      if(!$result){
         $error = var_export($query->errorInfo(), true);
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
         $errorMsgs .= "<br />".SystemMessages::$error['EmailRequired'][$lang];
      }
      if(!isset($this->hasAccount)){
         $errorMsgs .= "<br />".SystemMessages::$error['AccountRequired'][$lang];
      }
      if($this->hasAccount){
         if(!isset($this->accountName) || strcmp(trim($this->accountName), '') == 0 ){
            $errorMsgs .= "<br />".SystemMessages::$error['AccountNameRequired'][$lang];
         }
         if(!isset($this->isAutoBlock)){
            $errorMsgs .= "<br />".SystemMessages::$error['WhatBlockRequired'][$lang];
         }
      }
      if(!isset($this->blockingAdmin) || strcmp(trim($this->blockingAdmin), '') == 0){
         $errorMsgs .= "<br />".SystemMessages::$error['WhichAdminRequired'][$lang];
      }
      if(!isset($this->appeal) || strcmp(trim($this->appeal), '') == 0){
         $errorMsgs .= "<br />".SystemMessages::$error['NoReasonUnblock'][$lang];
      }
      if(!isset($this->blockReason) || strcmp(trim($this->blockReason), '') == 0){
         $errorMsgs .= "<br />".SystemMessages::$error['UserReasonNeedUnblock'][$lang];
      }
      if(!isset($this->intendedEdits) || strcmp(trim($this->intendedEdits), '') == 0){
         $errorMsgs .= "<br />".SystemMessages::$error['WhichEditsRequired'][$lang];
      }
      
      // validate fields
      if(!$emailErr){
         $email = $this->emailAddress;
         if(!validEmail($email)){
            $errorMsgs .= "<br />".SystemMessages::$error['ValidEmailRequired'][$lang];
         }
         $matches = array();
         if(preg_match(Appeal::$EMAIL_BLACKLIST, $email, $matches)){
            if($matches[1] == "mailinator"){
               $errorMsgs .= "<br />".SystemMessages::$error['NoMailinator'][$lang];
            }
            else{
               $errorMsgs .= "<br />".SystemMessages::$error['EmailBlacklisted'][$lang];
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
         return SystemMessages::$log['Userlink'][$lang] . $this->accountName;
      } else {
         return SystemMessages::$log['ContribsLink'][$lang] . $this->ipAddress;
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
   
   public function getBlockReason(){
      return $this->blockReason;
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
         $this->handlingAdminObject = UTRSUser::getUserById($this->handlingAdmin);
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

   public function getEmailToken() {
      return $this->emailToken;
   }
   
   public static function getAppealCountByIP($ip) {
      
      $db = connectToDB();
      
      $query = $db->prepare("
         SELECT COUNT(*) as count
         FROM appeal
         WHERE ip = :ip
            OR ip = :md5ip
            OR MD5(ip) = :iptwo");

      $result = $query->execute(array(
         ':ip'    => $ip,
         ':md5ip' => md5($ip),
         ':iptwo' => $ip)); // This is bound twice on purpose. -- cdhowie
      
      if(!$result){
         return 0;
      }
      
      $count = $query->fetchColumn();
      $query->closeCursor();

      return $count;
   }
   
   public function setStatus($newStatus){
      // TODO: query to check if status is closed; if so, whoever's reopening
      // should be a tool admin
      if(strcmp($newStatus, self::$STATUS_NEW) == 0 || strcmp($newStatus, self::$STATUS_AWAITING_USER) == 0
        || strcmp($newStatus, self::$STATUS_AWAITING_ADMIN) == 0 || strcmp($newStatus, self::$STATUS_AWAITING_CHECKUSER) == 0
        || strcmp($newStatus, self::$STATUS_AWAITING_PROXY) == 0 || strcmp($newStatus, self::$STATUS_CLOSED) == 0
        || strcmp($newStatus, self::$STATUS_ON_HOLD) == 0 || strcmp($newStatus, self::$STATUS_AWAITING_REVIEWER) == 0
      	|| strcmp($newStatus, self::$STATUS_INVALID) == 0) {
         // TODO: query to modify the row
         $this->status = $newStatus;
         if ($this->status == self::$STATUS_CLOSED) { UTRSSystemMessages::$log['Userlink'][$lang].getUserByUsername($_SESSION['user'])->incrementClose();
         }
      }
      else{
         // Note: this shouldn't happen
         throw new UTRSIllegalModificationException(SystemMessages::$error['InvalidStatus'][$lang]);
      }
   }
   
   public function setHandlingAdmin($admin, $saveadmin = 0){
      if($this->getHandlingAdminId() != null && $admin != null){
         throw new UTRSIllegalModificationException(SystemMessages::$error['AlreadyReserved'][$lang]);
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
      $this->handlingAdminObject = null;  // Invalidate cache

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
      
      $query = $db->prepare("
         UPDATE appeal
         SET lastLogId = :lastLogId
         WHERE appealID = :appealID");

      $result = $query->execute(array(
         ':lastLogId'   => $log_id,
         ':appealID' => $this->appealID));

      $this->lastLogId = $log_id;
   }

   public function verifyEmail($token) {
      if ($this->status !== self::$STATUS_UNVERIFIED) {
         throw new UTRSIllegalModificationException(SystemMessages::$error['EmailAlreadyVerified'][$lang]);
      }

      if ($token !== $this->emailToken) {
         throw new UTRSIllegalModificationException(SystemMessages::$error['InvalidEmailToken'][$lang]);
      }

      $db = ConnectToDB();

      $query = $db->prepare("
         UPDATE appeal
         SET status = :status,
             emailToken = NULL
         WHERE appealID = :appealID");

      $result = $query->execute(array(
         ':status'   => self::$STATUS_NEW,
         ':appealID' => $this->appealID));

	  /* On Wiki Notifications */
	  $bot = new UTRSBot();
	  $time = date('M d, Y H:i:s', time());
	  $bot->notifyUser($this->getCommonName(), array($this->appealID, $time));
	  
	  /* Change object and clean up */
      $this->status = self::$STATUS_NEW;
      $this->emailToken = null;
      $query->closeCursor();
   }
   public static function verifyBlock($username, $ipornot) {
      if ($ipornot) {
	   	  $data = json_decode(file_get_contents('http://en.wikipedia.org/w/api.php?action=query&list=users&ususers='.urlencode($username).'&format=json&usprop=blockinfo'),true);
	      $checkFound = False;
	      if (isset($data["query"]["users"][0]["blockid"])) {
	        $checkFound=True;
	      }
	      if (!$checkFound) { 
	        return False; 
	      }
	      return True;
      }
      else {
      	$data = json_decode(file_get_contents('http://en.wikipedia.org/w/api.php?action=query&list=blocks&bkip='.$username.'&format=json'),true);
      	$checkFound = False;
      	if (isset($data["query"]["blocks"][0]["id"])) {
      		$checkFound=True;
      	}
      	if (!$checkFound) {
      		return False;
      	}
      	return True;
      }
   }
   public static function verifyNoPublicAppeal($username) {
      //not sorting the api, seems to catch on pageid
      $data = file_get_contents('http://en.wikipedia.org/w/api.php?action=query&prop=revisions&rvlimit=1&rvprop=content&format=json&titles=User_talk:'.$username);
      $checkFound = False;
      $param = "^.*\{\{(U|u)nblock.*reviewed^";
      $reviewSearch = preg_match($param,$data);
      if ($reviewSearch !== 0) {
        $review = count(preg_match("^.*\{\{(U|u)nblock.*reviewed^",strtolower($data)));
        $unblock = count(preg_match("^.*\{\{(U|u)nblock^",$data)); 
        if ($review<$unblock) {
          //throw new UTRSValidationException(SystemMessages::$system['ReviewCount'][$lang] ".$review.", SystemMessages::$system['UnblockCount'][$lang]".$unblock);
          return False;
        }
        else { 
          return True; 
        }
      }
      else {
        //throw new UTRSValidationException(SystemMessages::$error['NoData'][$lang]"); 
        return True; 
      }
   }
   public static function activeAppeal($email,$wikiAccount) {
      $db = ConnectToDB();
	if (isset($wikiAccount)) {
      $query = $db->prepare("
         SELECT * FROM appeal
         WHERE (email =\"".$email."\"
          OR wikiAccountName = \"".$wikiAccount."\") AND (status !=\"closed\" AND status !=\"invalid\");");
	}
	else {
		$query = $db->prepare("
         SELECT * FROM appeal
         WHERE email =\"".$email."\" AND (status !=\"closed\" AND status !=\"invalid\");");
		}
      $result = $query->execute();
      if(!$result){
         $error = var_export($query->errorInfo(), true);
         throw new UTRSDatabaseException($error);
      }

      $values = $query->fetch(PDO::FETCH_ASSOC);
      $query->closeCursor();
      
      if ($values) {
        return True;
      }
      else {
        return False;
      }      
          
      
   }
   
   public function sendWMF() {
	   
		//TO Address
		$email		= "ca@wikimedia.org";
		
		//Headeers and FROm address
		$headers	= "From: Unblock Review Team <noreply-unblock@utrs.wmflabs.org>\r\n";
		$headers	.= "MIME-Version: 1.0\r\n";
		$headers	.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		
		//BODY
		$body		= SystemMessages::$system['WMFStaffAssist'][$lang] .
						"<a href=\"https://utrs.wmflabs.org/appeal.php?id=" . $this->getID() . ">".SystemMessages::$log['TicketNum'][$lang] . $this->getID() . "</a>." .
						"<br><br>" .
						SystemMessages::$system['BecauseWMF'][$lang];
						
		//SUBJECT
		$subject = SystemMessages::$log['WMFReq'][$lang] . $this->getID();
		
		//MAIL
		mail($email, $subject, $body, $headers);
	   
   }
}
class StatusButtonChecks {
	static function checkReserveRelease($appeal,$user) {
		global $lang;
		$disabled = "";
		if ($appeal->getHandlingAdmin()) {
			if (
					//When it is already in INVALID status
					$appeal->getStatus() == Appeal::$STATUS_INVALID ||
					//Not handling user and not admin
					$appeal->getHandlingAdmin()->getUserId() != $user->getUserId() && !verifyAccess($GLOBALS['ADMIN']) ||
					//In AWAITING_ADMIN status and not admin
					$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
					//Awaiting checkuser and not CU or admin
					$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
					//Appeal is closed and not an admin
					$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
			) {
				$disabled = " disabled = 'disabled' ";
			}
			return "<input type=\"button\" class=\"btn btn-danger " . $disabled . "\" value=\"".SystemMessages::$system['ReleaseButton'][$lang]."\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=release'\">&nbsp;";
		} else {
			if (
					//When it is already in INVALID status
					$appeal->getStatus() == Appeal::$STATUS_INVALID ||
					//Awaiting admin and not admin
					$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
					//Appeal awaiting CU and not CU or Admin
					$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
					//Appeal close and not admin
					$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
			) {
				$disabled = "disabled";
			}
			return "<input type=\"button\" class=\"btn btn-success " . $disabled . "\" value=\"".SystemMessages::$system['ReserveButton'][$lang]."\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=reserve'\">&nbsp;";
		}
	}
	static function checkNew($appeal,$user) {
		global $lang;
		$disabled = "";
		if (
				//When it is already in INVALID status and not a dev
				($appeal->getStatus() == Appeal::$STATUS_INVALID && !verifyAccess($GLOBALS['DEVELOPER'])) ||
				//Awaiting new
				$appeal->getStatus() == Appeal::$STATUS_NEW ||
				//When is assigned
				($appeal->getHandlingAdmin()) ||
				//Assigned and not CU or Admin
				!verifyAccess($GLOBALS['ADMIN']) ||
				//Awaiting admin and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_PROXY ||
				//Appeal is closed and not an admin
				$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
		) {
			$disabled = "disabled";
		}
		//return "<input type=\"button\" class=\"btn btn-default " . $disabled . "\"  value=\"SystemMessages::$system['ResetButton'][$lang]\" onClick=\"doNew()\">&nbsp;";
		return "<li class=" . $disabled . "><a href=\"#\" onClick=\"doNew()\">".SystemMessages::$system['ResetButton'][$lang]."</a></li>";
	}
	static function checkReturn($appeal,$user) {
		global $lang;
		$disabled = "";
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
				//Appeal needs to be reserved to send back to an admin
				!($appeal->getHandlingAdmin()) ||
				//Appeal is not in checkuser or admin status
				($appeal->getStatus() != Appeal::$STATUS_AWAITING_CHECKUSER && $appeal->getStatus() != Appeal::$STATUS_AWAITING_ADMIN && $appeal->getStatus() != Appeal::$STATUS_AWAITING_PROXY  && $appeal->getStatus() != Appeal::$STATUS_ON_HOLD) ||
				//Appeal is in checkuser status and user is not a checkuser or has the appeal assigned to them and not admin
				($appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !verifyAccess($GLOBALS['CHECKUSER']) /*|| $appeal->getHandlingAdmin() != $user*/) ||
				//For above, no one really cares if your the active handling admin for reviewing a CU req...
				//Appeal is in admin status and user is not admin
				($appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN'])) ||
				//If it is in the proxy queue, allow through
				//!($appeal->getStatus() == Appeal::$STATUS_AWAITING_PROXY) ||
				//There is no old handling admin - Not going to work, i've mod'd the comment to not require the old admin
				//$appeal->getOldHandlingAdmin() == null ||
				//Appeal is closed and not an admin
				($appeal->getStatus() == Appeal::$STATUS_CLOSED)
		) {
			$disabled = "disabled";
		}
		//return  "<input type=\"button\" class=\"btn btn-default " . $disabled . "\"  value=\"Back to Reviewing admin\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=return'\">&nbsp;";
		return "<li class=" . $disabled . "><a href=\"appeal.php?id=" . $_GET['id'] . "&action=status&value=return\"\">".SystemMessages::$system['ReviewerButton'][$lang]."</a></li>";
	}
	static function checkAwaitUser($appeal,$user){
		global $lang;
		$disabled = "";
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
				//When it is already in STATUS_AWAITING_USER status
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_USER ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//When not handling user and not admin
				!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
				//In AWAITING_ADMIN status and not admin
				($appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN'])) ||
				//Awaiting checkuser and not CU or admin
				($appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER']))) ||
				//Appeal is closed and not an admin
				($appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN']))
		) {
			$disabled = "disabled";
		}
		//return "<input type=\"button\" class=\"btn btn-default " . $disabled . "\" value=\"SystemMessages::$system['ResponseButton'][$lang]\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=user'\">&nbsp;";
		return "<li class=" . $disabled . "><a href=\"appeal.php?id=" . $_GET['id'] . "&action=status&value=user\"\">".SystemMessages::$system['ResponseButton'][$lang]."</a></li>";
	}
	static function checkInvalid($appeal,$user){
		global $lang;
		$disabled = "";
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
				//When not dev
				!verifyAccess($GLOBALS['DEVELOPER'])
		) {
			$disabled = "disabled";
		}
		//return "<input type=\"button\" class=\"btn btn-default " . $disabled . "\" value=\"SystemMessages::$system['InvalidButton'][$lang]\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=invalid'\">&nbsp;";
		return "<li class=" . $disabled . "><a href=\"appeal.php?id=" . $_GET['id'] . "&action=status&value=invalid\"\">".SystemMessages::$system['InvalidButton'][$lang]."</a></li>";
	}
	static function checkCheckuser($appeal,$user){
		global $lang;
		$disabled = "";
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
				//Awaiting checkuser (if it's already set to CU)
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//Assigned and not CU or Admin
				!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
				//Awaiting admin and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
				//Appeal is closed and not an admin
				$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
		) {
			$disabled = "disabled";
		}
		//return "<input type=\"button\" class=\"btn btn-default " . $disabled . "\"  value=\"Checkuser\" onClick=\"doCheckUser()\">&nbsp;";
		return "<li class=" . $disabled . "><a href=\"#\" onClick=\"doCheckuser()\">".SystemMessages::$system['CUButton'][$lang]."</a></li>";
	}
	static function checkHold($appeal,$user){
		global $lang;
		$disabled = "";
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
				//Already on hold
				$appeal->getStatus() == Appeal::$STATUS_ON_HOLD ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//When not handling user and not admin
				(!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN']))) ||
				//In AWAITING_ADMIN status and not admin
				($appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN'])) ||
				//Awaiting checkuser and not CU or admin
				($appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER']))) ||
				//Appeal is closed and not an admin
				($appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN']))
		) {
			$disabled = "disabled";
		}
		//return "<input type=\"button\" class=\"btn btn-default " . $disabled . "\"  value=\"Request a Hold\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=hold'\">&nbsp;".
		return "<li class=" . $disabled . "><a href=\"appeal.php?id=" . $_GET['id'] . "&action=status&value=hold\">".SystemMessages::$system['HoldButton'][$lang]."</a></li>".
			//"<input type=\"button\" class=\"btn btn-default " . $disabled . "\"  value=\"Blocking Admin\" id=\"adminhold\">&nbsp;".
			"<li class=" . $disabled . "><a href=\"#\" id=\"adminhold\">Request blocking admin</a></li>".
			//"<input type=\"button\" class=\"btn btn-default " . $disabled . "\"  value=\"WMF Staff\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=wmfhold'\">&nbsp;";
			"<li class=" . $disabled . "><a href=\"appeal.php?id=" . $_GET['id'] . "&action=status&value=wmfhold\">".SystemMessages::$system['WMFButton'][$lang]."</a></li>";
	}
	static function checkAwaitProxy($appeal,$user){
		global $lang;
		$disabled = "";
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
				//Already on proxy
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_PROXY ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//When not handling user and not admin
				!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
				//In AWAITING_ADMIN status and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
				//Awaiting checkuser and not CU or admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
				//Appeal is closed and not an admin
				$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
		) {
			$disabled = "disabled";
		}
		//return "<input type=\"button\" class=\"btn btn-default " . $disabled . "\"  value=\"SystemMessages::$system['ProxyButton'][$lang]\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=proxy'\">&nbsp;";
		return "<li class=" . $disabled . "><a href=\"appeal.php?id=" . $_GET['id'] . "&action=status&value=proxy\">".SystemMessages::$system['ProxyButton'][$lang]."</a></li>";
	}
	static function checkAwaitAdmin($appeal,$user){
		global $lang;
		$disabled = "";
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
				//Already on awaiting admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN
				//Only condition to allow an appeal to be sent to awaiting admin for any reason
		) {
			$disabled = "disabled";
		}
		//return "<input type=\"button\" class=\"btn btn-default " . $disabled . "\"  value=\"Tool Admin\" onClick=\"doAdmin()\">&nbsp;";
		return "<li class=" . $disabled . "><a href=\"#\" onClick=\"doAdmin()\">".SystemMessages::$system['ToolAdminButton'][$lang]."</a></li>";
	}
	static function checkClose($appeal,$user){
		global $lang;
		$disabled = "";
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
				//When set to AWAITING_ADMIN and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
				//Not handling user and not admin
				$appeal->getHandlingAdmin() != $user && !verifyAccess($GLOBALS['ADMIN']) ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//When closed
				$appeal->getStatus() == Appeal::$STATUS_CLOSED
		) {
			$disabled = "disabled";
		}
		return "<input type=\"button\" class=\"btn btn-danger " . $disabled . "\" value=\"".SystemMessages::$system['CloseAppeal'][$lang]."\" onClick=\"doClose();\">";
	}
}

?>