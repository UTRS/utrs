<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');
require_once('../src/userMgmtLogObject.php');


class User{
	
	private $username;
	private $userId;
	private $email;
	private $wikiAccount;
	private $approved;
	private $active;
	private $toolAdmin;
	private $checkuser;
	private $useSecure;
	private $passwordHash;
	
	public function __construct(array $vars, $fromDB){
		debug('in constructor for user <br/>');
		if($fromDB){
			$this->username = $vars['username'];
			$this->userID = $vars['userID'];
			$this->email = $vars['email'];
			$this->wikiAccount = $vars['wikiAccount'];
			$this->approved = ($vars['approved'] == 1 || $vars['approved'] == '1' ? true : false);
			$this->active = ($vars['active'] == 1 || $vars['active'] == '1' ? true : false);
			$this->toolAdmin = ($vars['toolAdmin'] == 1 || $vars['toolAdmin'] == '1' ? true : false);
			$this->checkuser = ($vars['checkuser'] == 1 || $vars['checkuser'] == '1' ? true : false);
			$this->passwordHash = $vars['passwordHash'];
			$this->useSecure = ($vars['useSecure'] == 1 || $vars['useSecure'] == '1' ? true : false);
		}
		else{
			$this->username = $vars['username'];
			$this->email = $vars['email'];
			$this->wikiAccount = $vars['wikiAccount'];
			$this->approved = 0;
			$this->active = 0;
			$this->toolAdmin = 0;
			$this->checkuser = 0;
			$this->useSecure = isset($vars['useSecure']);
			$this->passwordHash = hash("sha512", $vars['password']);
			
			$this->insert();
		}
		debug('leaving user constructor <br/>');
	}
	
	public function insert(){
		debug('in insert for User <br />');
		
		$db = connectToDB();
		
		$query = 'INSERT INTO user (username, email, wikiAccount, useSecure, passwordHash)';
		$query .= ' VALUES (\'' . $this->username . '\', ';
		$query .= '\'' . $this->email . '\', ';
		$query .= '\'' . $this->wikiAccount . '\', ';
		$query .= '\'' . $this->useSecure . '\', ';
		$query .= '\'' . $this->passwordHash . '\')';
		
		debug($query . '<br/>');
		
		$result = mysql_query($query, $db);
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		debug('Insert complete <br/>');
		
		$this->userId = mysql_insert_id($db);
		
		UserMgmtLog::insert('created account', 'New account', $this->userId, $this->userId);
		
		debug('exiting user insert <br/>');
	}
	
	public static function getUserById($id){
		$db = connectToDB();
		
		$query = 'SELECT * FROM user WHERE userID=\'' . $id . '\'';
		
		$result = mysql_query($query, $db);
		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}
		if(mysql_num_rows($result) == 0){
			throw new UTRSDatabaseException('No results were returned for user ID ' . $id);
		}
		if(mysql_num_rows($result) != 1){
			throw new UTRSDatabaseException('Please contact a tool developer. More '
				. 'than one result was returned for user ID ' . $id);
		}
		
		$values = mysql_fetch_assoc($result);
		
		return new User($values, true);
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
	
	public function getEmail(){
		return $this->email;
	}
}

?>