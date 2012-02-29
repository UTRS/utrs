<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('exceptions.php');
require_once('unblocklib.php');
require_once('userObject.php');


class UserMgmtLog{

	private $logId;
	private $action;
	private $reason;
	private $timestamp;
	private $target;
	private $doneBy;
	private $hideTarget;
	
	public function __construct(array $vars){
		debug('in constructor for MgmtLog');
		
		$this->logId = $vars['logID'];
		$this->action = $vars['action'];
		$this->reason = $vars['reason'];
		$this->timestamp = $vars['timestamp'];
		$this->target = User::getUserById($vars['target']);
		$this->doneBy = User::getUserById($vars['doneBy']);
		$this->hideTarget = $vars['hideTarget'];
	}

	public static function insert($logAction, $logReason, $targetUserId, $doneByUserId, $hideTarget = 0){
		debug('in insert for userMgmtLog<br/>');
		
		$db = connectToDB();
		
		$query = 'INSERT INTO userMgmtLog (action, reason, target, doneBy, hideTarget) VALUES (';
		$query .= '\'' . $logAction . '\', ';
		$query .= '\'' . $logReason . '\', ';
		$query .= '\'' . $targetUserId . '\', ';
		$query .= '\'' . $doneByUserId . '\', ';
		$query .= '\'' . $hideTarget . '\')';
		
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
		if(!$this->hideTarget){
			$string .= $this->target->getUsername() . ' ';
		}
		$string .= '(<i>' . $this->reason . '</i>)';
		
		return $string;
	}
}

?>