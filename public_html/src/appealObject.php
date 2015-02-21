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
   public static $TOOLSERVER_IPS = '91.198.174.197,91.198.174.204,185.15.59.204,185.15.59.197,10.4.1.89,tools.wmflabs.org,10.4.0.78,10.68.16.65,dynamicproxy-gateway.eqiad.wmflabs';
   /**
    * The appeal has not yet passed email verification
    */
   public static $STATUS_UNVERIFIED = 'UNVERIFIED';
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
      $appeal = new Appeal($values);

      $appeal->ipAddress = self::getIPFromServer();
      $appeal->status = self::$STATUS_UNVERIFIED;
      $appeal->handlingAdmin = null;
      $appeal->handlingAdminObject = null;

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
         throw new UTRSDatabaseException('No results were returned for appeal ID ' . $id);
      }
      
      return self::newTrusted($values);
   }
   
   public static function getCheckUserData($appealID) {
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
      if(!isset($this->blockReason) || strcmp(trim($this->blockReason), '') == 0){
         $errorMsgs .= "<br />You have not told us what you think the reason you are blocked is.";
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
        || strcmp($newStatus, self::$STATUS_ON_HOLD) == 0 || strcmp($newStatus, self::$STATUS_AWAITING_REVIEWER) == 0){
         // TODO: query to modify the row
         $this->status = $newStatus;
         if ($this->status == self::$STATUS_CLOSED) {
            User::getUserByUsername($_SESSION['user'])->incrementClose();
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
         throw new UTRSIllegalModificationException('The email address for this appeal has already been verified.');
      }

      if ($token !== $this->emailToken) {
         throw new UTRSIllegalModificationException('Invalid email confirmation token.  Please ensure that you have copied and pasted the verification URL correctly.');
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

      $this->status = self::$STATUS_NEW;
      $this->emailToken = null;
   }
   public function verifyBlock($username) {
      $data = json_decode(file_get_contents('http://en.wikipedia.org/w/api.php?action=query&list=users&ususers='.$username.'&format=json&usprop=blockinfo'),true);
      $checkFound = False;
      foreach ($data["query"]["users"][0] as $i => $value) {
        #This method below is crude...but it's better than playing with fatal errors
        if (strtolower($value) == strtolower($username)) {
          $checkFound=True;
        }
      }
      if (!$checkFound) { 
        return False; 
      }
      return True;
   }
   public function verifyNoPublicAppeal($username) {
      $data = json_decode(file_get_contents('http://en.wikipedia.org/w/api.php?action=query&prop=revisions&rvlimit=1&rvprop=content&format=json&titles=User_talk:'.$username),true);
      $page = json_decode(file_get_contents('http://en.wikipedia.org/w/api.php?action=query&list=allpages&apnamespace=3&apfrom='.$username),true);
      $pageid = $page["query"]["allpages"][0]["pageid"];
      $checkFound = False;
      $param = "^.*\{\{(U|u)nblock.*reviewed^";
      $content = $data["query"];
      $content = $content["pages"];
      $temp = array_values(content)[0];
      $content = $content[$temp];
      $content = $content["revisions"];
      $content = $content["\*"];
      $reviewSearch = preg_match($param,$content);
      if ($reviewSearch !== 0) {
        if (count(preg_match("^.*\{\{(U|u)nblock.*reviewed^",strtolower($data["query"]["pages"][$pageid]["revisions"]["*"]))<count(preg_match("^.*\{\{(U|u)nblock^",$data["query"]["pages"][$pageid]["revisions"]["*"])))) {
          return False;
        }
      }
      else { 
        return True; 
      }
   }
}

?>
