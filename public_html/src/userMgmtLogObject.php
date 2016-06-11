<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('exceptions.php');
require_once('unblocklib.php');
require_once('userObject.php');
require_once('messages.php');


class UserMgmtLog{

	private $logId;
	private $action;
	private $change;
	private $reason;
	private $timestamp;
	private $target;
	private $doneBy;
	private $hideTarget;
	
	public function __construct(array $vars){
		debug('in constructor for MgmtLog');
		
		$this->logId = $vars['logID'];
		$this->action = $vars['action'];
		$this->change = $vars['change'];
		$this->reason = $vars['reason'];
		$this->timestamp = $vars['timestamp'];
		$this->target = User::getUserById($vars['target']);
		$this->doneBy = User::getUserById($vars['doneBy']);
		$this->hideTarget = $vars['hideTarget'];
	}

	public static function insert($logAction, $logChange, $logReason, $targetUserId, $doneByUserId, $hideTarget = 0){
		debug('in insert for userMgmtLog<br/>');
		
		if($hideTarget === true){
			$hideTarget = 1;
		}
		
		$db = connectToDB();

		$query = $db->prepare('INSERT INTO userMgmtLog (`action`, `change`, `reason`, `target`, `doneBy`, `hideTarget`) VALUES (:action, :change, :reason, :target, :doneBy, :hideTarget)');

		$result = $query->execute(array(
			':action'	=> $logAction,
			':change'   => $logChange,
			':reason'	=> $logReason,
			':target'	=> $targetUserId,
			':doneBy'	=> $doneByUserId,
			':hideTarget'	=> $hideTarget));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		debug('Insert complete <br/>');
	}
	
	public function toString(){		
		$string = ''. $this->timestamp . ' - ';
		$string .= $this->doneBy->getUsername() . ' ';
		$string .= $this->action . ' ';
		if($this->hideTarget == 0){
			$string .= $this->target->getUsername() . ' ';
		}
		$string .= '(<i>' . $this->reason . '</i>)';
		
		return $string;
	}
}

?>
