<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');

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
		
			$this->target = $values['target'];
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
		}
		debug('Exiting constuctor <br/>');
	}
	
	public static function getBanByID($id){
		$db = connectToDB();
		
		$query = 'SELECT * FROM banList ';
		$query .= 'WHERE banID = \'' . $id . '\'';
		
		$result = mysql_query($query, $db);
		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}
		if(mysql_num_rows($result) == 0){
			throw new UTRSDatabaseException('No results were returned for ban ID ' . $id);
		}
		if(mysql_num_rows($result) != 1){
			throw new UTRSDatabaseException('Please contact a tool developer. More '
				. 'than one result was returned for ban ID ' . $id);
		}
		
		$values = mysql_fetch_assoc($result);
		
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
		$query = "DELETE FROM banList WHERE target='" . $this->getTarget() . "'";
		mysql_query($query);
		// not checking for errors here, if this fails it's probably ok
		$time = time();
		$format = "Y-m-d H:i:s";
		$hasExpiry = isset($values['durationAmt']) && strlen($values['durationAmt']) == 0;
		debug("Has expiry: " . $hasExpiry . "\n");
		
		$query = "INSERT INTO banList (target, timestamp, expiry, reason, admin) VALUES ('";
		$query .= $this->target . "', '";
		$query .= date($format, $time) . "', ";
		if($hasExpiry){
			// "+3 days"
			$query .= "'" . date($format, strtotime("+" . $values['durationAmt'] . " " . $values['durationUnit'], $time)) . "', '";
		} 
		else{
			$query .= mysql_escape_string("'NULL'") . ", ";
		}
		$query .= mysql_real_escape_string($this->reason) . "', '";
		$query .= $this->admin->getUserId() . "')";
		
		debug($query);
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			throw new UTRSDatabaseException(mysql_error($db));
		}
		
		$this->banID = mysql_insert_id($db);
		$query = "SELECT timestamp, expiry FROM banList WHERE banID='" . $this->banID . "'";
		
		debug($query);
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			throw new UTRSDatabaseException(mysql_error($db));
		}
		
		$data = mysql_fetch_assoc($result);
		$this->timestamp = $data['timestamp'];
		$this->expiry = $data['expiry'];
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
	
	public function delete(){
		$db = connectToDB();
		
		$query = "DELETE FROM banList WHERE banID='" . $this->banID . "'";
		
		debug($query);
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			throw new UTRSDatabaseException(mysql_error($db));
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
		$target = "target='";
		if($ip){
			$target .= $ip . "'";
		}
		if($email){
			if($target){
				$target .= " OR target='";
			}
			$target .= $email . "'";
		}
		if($name){
			if($target){
				$target .= " OR target='";
			}
			$target .= $name . "'";
		}
		
		// get a list of indefinite bans on the target
		$query = "SELECT * FROM banList WHERE (" . $target . ") AND expiry IS NULL";
		
		$db = connectToDB();
		
		debug($query);
		
		$result = mysql_query($query);
		
		if(!$result){
			throw new UTRSDatabaseException(mysql_error($db));
		}
		
		if(mysql_num_rows($result) > 0){
			// if any, return one, doesn't matter which
			$data = mysql_fetch_assoc($result);
			return new Ban($data, true);
		}
		
		// if no indefinites, grab list of all other bans, longest first
		$query = "SELECT * FROM banList WHERE (" . $target . ") AND expiry IS NOT NULL AND expiry > NOW() ORDER BY expiry DESC";
		
		debug($query);
		
		$result = mysql_query($query);
		
		if(!$result){
			throw new UTRSDatabaseException(mysql_error($db));
		}
		
		if(mysql_num_rows($result) > 0){
			// if any, return first one
			$data = mysql_fetch_assoc($result);
			return new Ban($data, true);
		}
		
		// if none, return false
		return false;
	}
	
	/**
	 * Returns all active bans as objects in an array, or false if there are none. Lower indices expire first.
	 * @throws UTRSDatabaseException
	 */
	public static function getAllActiveBans(){
		// get all active bans, soonest to expire first
		$query = "SELECT * FROM banList WHERE expiry IS NULL OR expiry > NOW() ORDER BY expiry ASC";
		
		$db = connectToDB();
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			throw new UTRSDatabaseException(mysql_error($db));
		}
		
		$rows = mysql_num_rows($result);
		
		if($rows == 0){
			return false;
		}
		
		$bans = array();
		
		for($i = 0; $i < $rows; $i++){
			$data = mysql_fetch_assoc($result);
			$bans[$i] = new Ban($data, true);
		}
		
		return $bans;
	}
}