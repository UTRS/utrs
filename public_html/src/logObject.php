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
	
	public static function __construct($vars) {
		$this->commentID = $vars['commentID'];
		$this->appealID = $vars['appealID'];
		$this->timestamp = $vars['timestamp'];
		$this->comment = $vars['comment'];
		$this->commentUser = $vars['commentUser'];
	}
	
	function getComment() {
		return new Array($commentID, $appealID, $timestamp, $comment, $commentUser);
	}
}
class Log {
	
	private $log = new Array();
	private $Count = -1;
	
	public static function __construct($vars) {
		if ($vars) {
			
			$num_rows = mysql_num_rows($vars)
			
			for ($i = 0; $i < $num_rows; $i++) {
				//Creates a new log item with the data
				$data = mysql_fetch_aray($vars);
				$log[$i] = new LogItem($data);
				$Count = $i;
			}
		} else {
			return null;
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
		if(mysql_num_rows($result) == 0){
			return null;
		}
		
		return $result;
	}
	
	public static function addNewItem($action, $appealID) {
		$db = connectToDB();
		
		$user = User::getUserByUsername($_SESSION['user']);
		
		$action = mysql_real_escape_string($action)
		
		$timestamp = date();
		
		$query = "INSERT INTO comment (appealID, timestamp, comment, commentUser) VALUES ("
		$query .= $appealID . ", ";
		$query .= $timestamp . ", ";
		$query .= $action . ", ";
		$query .= $user->getID() . ");";
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}
		
		$id = mysql_insert_id($db);
		
		$log[$Count + 1] = new LogItem(new Array('commentID' => $id, 'appealID' => $appealID, 'timestamp' => $timestamp, 'comment' => $comment, 'commentUser', $commentUser));
		$Count++;
	}
	
	public static function getSmallHTML() {
		
		$HTMLOutput = "";
		
		$HTMLOutput .= "<table class=\"logTable\">";
		$HTMLOutput .= "<tr>";
		$HTMLOutput .= "<td class=\"logHeader\">User</td>";
		$HTMLOutput .= "<td class=\"logHeader\">Action</td>";
		$HTMLOutput .= "</tr>";
		
		for ($i = 0; $i < $log.length; $i++) {
			
		}
	}
}

?>