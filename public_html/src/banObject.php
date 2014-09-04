<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('exceptions.php');
require_once('unblocklib.php');

class Ban{
	
	/**
	 * Database ID number
	 */
	private $banID;
	/**
	 * The IP address, email, or account name the ban is directed at
	 */
	private $target;
	/**
	 * The time the block was placed
	 */
	private $timestamp;
	/**
	 * The time the block expires
	 */
	private $expiry;
	/**
	 * The reason for the ban
	 */
	private $reason;
	/**
	 * The banning tool administrator
	 */
	private $admin;
	/**
	 * Is this targeted at an IP?
	 */
	private $isIP;
		
	/**
	 * Build a Ban object. If $fromDB is true, the mappings in $values
	 * will be assumed to be those from the database; additionally,
	 * the values will not be validated and the object will not be inserted to
	 * the DB. Otherwise, the mappings in $values will be assumed to be those
	 * from the "new ban" form; values will be validated, and the object
	 * will be inserted into the DB on completion.
	 * 
	 * @param array $values the information to include in this appeal
	 * @param boolean $fromDB is this from the database?
	 */
	public function __construct(array $values, $fromDB = false){
		debug('In constuctor for Ban <br/>');
		if(!$fromDB){
			debug('Obtaining values from form <br/>');
			Ban::validate($values); // may throw an exception
			$setTarget = $values['target'];
			
			if(preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $values['target']) == 1){
				$setTarget = md5($values['target']);
				$this->isIP = true;
			}
			else{
				$this->isIP = false;
			}
		
			$this->target = $setTarget;
			$this->admin = getCurrentUser();
			$this->reason = $values['reason'];
			
			debug('Setting values complete <br/>');
			
			Ban::insert($values);
		}
		else{
			debug('Obtaining values from DB <br/>');
			$this->banID = $values['banID'];
			$this->target = $values['target'];
			$this->timestamp = $values['timestamp'];
			$this->expiry = $values['expiry'];
			$this->reason = $values['reason'];
			$this->admin = User::getUserById($values['admin']);
			$this->isIP = ($values['isIP'] == 1 ? true : false);
		}
		debug('Exiting constuctor <br/>');
	}
	
	public static function getBanByID($id){
		$db = connectToDB();
		
		$query = $db->prepare('SELECT * FROM banList WHERE banID = :banID');

		$result = $query->execute(array(
			':banID'	=> $id));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}

		$values = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();

		if($values === false){
			throw new UTRSDatabaseException('No results were returned for ban ID ' . $id);
		}
		
		return new Ban($values, true);
	}
	
	public static function validate(array $values){
		if(isset($values['durationAmt']) && strlen($values['durationAmt']) != 0 && !preg_match("/^[0-9]{1,}$/", $values['durationAmt'])){
			throw new UTRSIllegalModificationException("Duration must be a positive number.");
		}
		if(isset($values['durationAmt']) && strlen($values['durationAmt']) != 0 && (!isset($values['durationUnit']) || strlen($values['durationUnit']) == 0)){
			throw new UTRSIllegalModificationException("You must select a unit of time if you set a duration.");
		}
		if(!isset($values['reason']) || strlen($values['reason']) === 0){
			throw new UTRSIllegalModificationException("You must provide a reason!");
		}
		if(strlen($values['reason']) > 1024){
			throw new UTRSIllegalModificationException("Your reason must be less than 1024 characters.");
		}
		// if NOT ip address AND NOT email AND contains one of the characters
		if(preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $values['target']) == 0
		  && !validEmail($values['target'])
		  && (strpos($values['target'], "#") !== false || strpos($values['target'], "/") !== false ||
		   strpos($values['target'], "|") !== false || strpos($values['target'], "[") !== false ||
		   strpos($values['target'], "]") !== false || strpos($values['target'], "{") !== false ||
		   strpos($values['target'], "}") !== false || strpos($values['target'], "<") !== false ||
		   strpos($values['target'], ">") !== false || strpos($values['target'], "@") !== false ||
		   strpos($values['target'], "%") !== false || strpos($values['target'], ":") !== false || 
		   strpos($values['target'], '$') !== false)){
		   	throw new UTRSIllegalModificationException("The target must be an IP address, email address, or valid Wikipedia username");
		}
	}
	
	private function insert(array $values){
		$db = connectToDB();
		
		// safety - remove any existing bans on the same target
		$query = $db->prepare("DELETE FROM banList WHERE target = :target");
		$query->execute(array(
			':target'	=> $this->getTarget()));
		// not checking for errors here, if this fails it's probably ok

		$time = time();
		$format = "Y-m-d H:i:s";
		$hasExpiry = isset($values['durationAmt']) && strlen($values['durationAmt']) != 0;
		debug("Has expiry: " . $hasExpiry . "\n");
		
		$query = $db->prepare("
			INSERT INTO banList
			(target, timestamp, expiry, reason, admin, isIP)
			VALUES (:target, :timestamp, :expiry, :reason, :admin, :isIP)");

		if ($hasExpiry) {
			$expiry = date($format, strtotime("+" . $values['durationAmt'] . " " . $values['durationUnit'], $time));
		} else {
			$expiry = null;
		}

		$timestamp = date($format, $time);

		$result = $query->execute(array(
			':target'	=> $this->target,
			':timestamp'	=> $timestamp,
			':expiry'	=> $expiry,
			':reason'	=> $this->reason,
			':admin'	=> $this->admin->getUserId(),
			':isIP'		=> (bool)$this->isIP));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}
		
		$this->banID = $db->lastInsertId();
		$this->timestamp = $timestamp;
		$this->expiry = $expiry;
	}
	
	public function getBanID(){
		return $this->banID;
	}
	
	public function getTarget(){
		return $this->target;
	}
	
	public function getTimestamp(){
		return $this->timestamp;
	}
	
	public function getExpiry(){
		return $this->expiry;
	}
	
	public function getReason(){
		return $this->reason;
	}
	
	public function getAdmin(){
		return $this->admin;
	}
	
	public function isIP(){
		return $this->isIP;
	}
	
	public function delete(){
		$db = connectToDB();
		
		$query = $db->prepare("DELETE FROM banList WHERE banID = :banID");

		$result = $query->execute(array(
			':banID'	=> $this->banID));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}
		
		$this->target = null;
		$this->reason = null;
		$this->timestamp = null;
		$this->expiry = null;
		$this->admin = null;
		$this->banID = null;
	}
	
	/**
	 * Returns the current ban of longest duration against any of the given targets
	 * @param string $ip the IP address
	 * @param string $email the email address
	 * @param string $name the username (can be null)
	 * @throws UTRSDatabaseException if there's a problem
	 * @return false if no ban applies to any of the given targets, or the Ban object
	 *   with the longest duration (indefinite is longest) if a ban does apply
	 */
	public static function isBanned($ip, $email, $name){
		// if null or blank
		if(!$ip && !$email && !$name){
			return false;
		}
		
		$ip = md5($ip);
		
		$db = connectToDB();

		$params = array();
		$pieces = array();

		if($ip){
			$params[':ip'] = $ip;
			$pieces[] = 'target = :ip';
		}
		if($email){
			$params[':email'] = $email;
			$pieces[] = 'target = :email';
		}
		if($name){
			$params[':name'] = $name;
			$pieces[] = 'target = :name';
		}

		if (count($pieces) == 0) {
			// All arguments had no value; no point in checking anything.
			return false;
		}
		
		// Changed this to work with one query only, the two ORDER BY
		// clauses cause NULL expiry (indefinite) to be sorted first,
		// then the rest by expiry time.
		$query = $db->prepare("
			SELECT * FROM banList
			WHERE (" . implode(' OR ', $pieces) . ")
			  AND (expiry IS NULL OR expiry > NOW())
			ORDER BY expiry IS NULL DESC, expiry DESC");
		
		$result = $query->execute($params);
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}

		$data = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();

		if ($data !== false) {
			return new Ban($data, true);
		}
		
		// if none, return false
		return false;
	}
	
	/**
	 * Returns all active bans as objects in an array. Lower indices expire first.
	 * @throws UTRSDatabaseException
	 */
	public static function getAllActiveBans(){
		// get all active bans, soonest to expire first
		$db = connectToDB();

		$query = $db->prepare("SELECT * FROM banList WHERE expiry IS NULL OR expiry > NOW() ORDER BY expiry ASC");
		
		$result = $query->execute();
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}
		
		$bans = array();
		
		while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
			$bans[] = new Ban($data, true);
		}

		$query->closeCursor();
		
		return $bans;
	}
}
