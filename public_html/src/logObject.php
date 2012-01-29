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
		return array($commentID, $appealID, $timestamp, $comment, $commentUser);
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
				$data = mysql_fetch_aray($vars['dataset']);
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
		
		$user = User::getUserByUsername($_SESSION['user']);
		
		$action = mysql_real_escape_string($action);
		
		$timestamp = date();
		
		$query = "INSERT INTO comment (appealID, timestamp, comment, commentUser) VALUES (";
		$query .= $this->appealID . ", ";
		$query .= $timestamp . ", ";
		$query .= $action . ", ";
		$query .= $user->getID() . ");";
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}
		
		$id = mysql_insert_id($db);
		
		$this->log[$this->Count + 1] = new LogItem(array('commentID' => $id, 'appealID' => $this->appealID, 'timestamp' => $timestamp, 'comment' => $comment, 'commentUser', $commentUser));
		$this->Count++;
	}
	
	public function getSmallHTML() {
		
		$HTMLOutput = "";
		
		$HTMLOutput .= "<table class=\"logTable\">";
		$HTMLOutput .= "<tr>";
		$HTMLOutput .= "<td class=\"logUserHeader\">User</td>";
		$HTMLOutput .= "<td class=\"logActionHeader\">Action</td>";
		$HTMLOutput .= "</tr>";
		
		for ($i = 0; $i < count($this->log); $i++) {
			$styleUser = ($i%2 == 1) ? "smallLogUserOne" : "smallLogUserTwo";
			$styleAction = ($i%2 == 1) ? "smallLogActionOne" : "smallLogActionTwo";
			$HTMLOutput .= "<tr>";
			$HTMLOutput .= "<td class=\"" . $style . "\">" . $this->log[$i]->getLogArray()->commentUser . "</td>";
			$HTMLOutput .= "<td class=\"" . $style . "\">" . $this->log[$i]->getLogArray()->comment . "</td>";
			$HTMLOutput .= "</tr>";
		}
		
		$HTMLOutput .= "</table>";
		
		return $HTMLOutput;
	}
}

?>