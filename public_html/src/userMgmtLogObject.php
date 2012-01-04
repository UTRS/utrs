<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');
require_once('../src/userObject.php');


class UserMgmtLog{

	private $logId;
	private $action;
	private $reason;
	private $timestamp;
	private $target;
	private $doneBy;
	
	public function __construct(array $vars){
		debug('in constructor for MgmtLog');
		
		$this->logId = $vars['logID'];
		$this->action = $vars['action'];
		$this->reason = $vars['reason'];
		$this->timestamp = $vars['timestamp'];
		$this->target = User::getUserById($vars['target']);
		$this->doneBy = User::getUserById($vars['doneBy']);
	}

	public static function insert($logAction, $logReason, $targetUserId, $doneByUserId){
		debug('in insert for userMgmtLog<br/>');
		
		$db = connectToDB();
		
		$query = 'INSERT INTO userMgmtLog (action, reason, target, doneBy) VALUES (';
		$query .= '\'' . $logAction . '\', ';
		$query .= '\'' . $logReason . '\', ';
		$query .= '\'' . $targetUserId . '\', ';
		$query .= '\'' . $doneByUserId . '\')';
		
		$result = mysql_query($query, $db);
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		debug('Insert complete <br/>');
	}
	
	public function toString(){		
		$string = ''. $this->timestamp . ' - ';
		$string .= $this->doneBy->getUsername() . ' ';
		$string .= $this->action . ' ';
		$string .= $this->target->getUsername() . ' (<i>';
		$string .= $this->reason . '</i>)';
		
		return $string;
	}
}

?>