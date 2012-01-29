<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');

// TODO: Add static methods to get objects from database by ID and status
// Can't overload the constructor, so the existing one may need to be modified

/**
 * This class contains information relevant to a single unblock appeal.
 * 
 */
class LogItem {
	private $commentID;
	private $appealID;
	private $timestamp;
	private $comment;
	private $commentUser;
	
	public function __construct($vars) {
		$this->commentID = $vars['commentID'];
		$this->appealID = $vars['appealID'];
		$this->timestamp = $vars['timestamp'];
		$this->comment = $vars['comment'];
		$this->commentUser = $vars['commentUser'];
	}
	
	function getLogArray() {
		return array('commentID' => $this->commentID, 'appealID' => $this->appealID, 'timestamp' => $this->timestamp, 'comment' => $this->comment, 'commentUser' => $this->commentUser);
	}
}
class Log {
	
	private $log = array();
	private $Count = -1;
	private $appealID;
	
	public function __construct($vars) {
		if ($vars) {
			$this->appealID = $vars['appealID'];
			$num_rows = mysql_num_rows($vars['dataset']);
			
			for ($i = 0; $i < $num_rows; $i++) {
				//Creates a new log item with the data
				$data = mysql_fetch_array($vars['dataset']);
				$this->log[$i] = new LogItem($data);
				$this->Count = $i;
			}
		}
	}
	
	public static function getCommentsByAppealId($id) {
		$db = connectToDB();
		
		$query = "SELECT * from comment WHERE appealID = " . $id;
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}
		
		return new Log(array('dataset' => $result, 'appealID' => $id));
	}
	
	public function addNewItem($action) {
		$db = connectToDB();
		
		if (isset($_SESSION)) {
			$user = User::getUserByUsername($_SESSION['user']);
			$userid = $user->getUserId();
		} else {
			$userid = null;
		}
		
		$action = mysql_real_escape_string($action);
		
		$timestamp = time();
		
		$query = "INSERT INTO comment (appealID, timestamp, comment, commentUser) VALUES (";
		$query .= $this->appealID . ", ";
		$query .= "NOW() , '";
		$query .= mysql_real_escape_string($action) . "', ";
		$query .= $userid . ");";
		
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}
		
		$id = mysql_insert_id($db);
		
		$this->log[$this->Count + 1] = new LogItem(array('commentID' => $id, 'appealID' => $this->appealID, 'timestamp' => $timestamp, 'comment' => $action, 'commentUser' => $user->getUserId()));
		$this->Count++;
	}
	
	public function getSmallHTML() {
		
		$HTMLOutput = "";
		
		$HTMLOutput .= "<table class=\"logTable\">";
		$HTMLOutput .= "<tr>";
		$HTMLOutput .= "<th class=\"logUserHeader\">User</th>";
		$HTMLOutput .= "<th class=\"logActionHeader\">Action</th>";
		$HTMLOutput .= "</tr>";
		
		for ($i = 0; $i < count($this->log); $i++) {
			$styleUser = ($i%2 == 1) ? "smallLogUserOne" : "smallLogUserTwo";
			$styleAction = ($i%2 == 1) ? "smallLogActionOne" : "smallLogActionTwo";
			$data = $this->log[$i]->getLogArray();
			$username = ($data['commentUser']) ? User::getUserById($data['commentUser'])->getUserName() : "";
			$HTMLOutput .= "<tr>";
			$HTMLOutput .= "<td class=\"" . $styleUser . "\">" . $username . "</td>";
			$HTMLOutput .= "<td class=\"" . $styleAction . "\">" . substr($data['comment'],0,50) . "</td>";
			$HTMLOutput .= "</tr>";
		}
		
		$HTMLOutput .= "</table>";
		
		return $HTMLOutput;
	}
	
	public function getLargeHTML() {
	
		$HTMLOutput = "";
	
		$HTMLOutput .= "<table class=\"logLargeTable\">";
		$HTMLOutput .= "<tr>";
		$HTMLOutput .= "<th class=\"logLargeUserHeader\">User</th>";
		$HTMLOutput .= "<th class=\"logLargeActionHeader\">Action</th>";
		$HTMLOutput .= "<th class=\"logLargeUserHeader\">Timestamp</th>";
		$HTMLOutput .= "</tr>";
	
		for ($i = 0; $i < count($this->log); $i++) {
			$styleUser = ($i%2 == 1) ? "largeLogUserOne" : "largeLogUserTwo";
			$styleAction = ($i%2 == 1) ? "largeLogActionOne" : "largeLogActionTwo";
			$data = $this->log[$i]->getLogArray();
			$timestamp = (is_numeric($data['timestamp']) ? date("Y-m-d H:m:s", $data['timestamp']) : $data['timestamp']);
			$username = ($data['commentUser']) ? User::getUserById($data['commentUser'])->getUserName() : "";
			$HTMLOutput .= "<tr>";
			$HTMLOutput .= "<td valign=top class=\"" . $styleUser . "\">" . $username . "</td>";
			$HTMLOutput .= "<td valign=top class=\"" . $styleAction . "\">" . str_replace("\r\n", "<br>", $data['comment']) . "</td>";
			$HTMLOutput .= "<td valign=top class=\"" . $styleUser . "\">" . $timestamp . "</td>";
			$HTMLOutput .= "</tr>";
		}
	
		$HTMLOutput .= "</table>";
	
		return $HTMLOutput;
	}
}

?>