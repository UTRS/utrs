<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('exceptions.php');
require_once('unblocklib.php');

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
	private $action;

	public function __construct($vars) {
		$this->commentID = $vars['commentID'];
		$this->appealID = $vars['appealID'];
		$this->timestamp = $vars['timestamp'];
		$this->comment = $vars['comment'];
		$this->commentUser = $vars['commentUser'];
		$this->action = $vars['action'];
	}

	function getLogArray() {
		return array('commentID' => $this->commentID, 'appealID' => $this->appealID, 'timestamp' => $this->timestamp, 'comment' => $this->comment, 'commentUser' => $this->commentUser, 'action' => $this->action);
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

		$query = "SELECT * from comment WHERE appealID = " . $id . " ORDER BY timestamp ASC;";

		$result = mysql_query($query, $db);

		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}

		return new Log(array('dataset' => $result, 'appealID' => $id));
	}

	public function addNewItem($comment, $action = null, $username = false) {
		$db = connectToDB();
		if (!$username && (isset($_SESSION['user']) && strlen($_SESSION['user']) != 0)) {
			$username = $_SESSION['user'];
		}
		if (!$username){
			$firstuserid = "null";
			$seconduserid = null;
		} else {
			$firstuserid = User::getUserByUsername($username)->getUserId();
			$seconduserid = $fistuserid;
		}
		
		if (!$action) {
			$action = 0;
		}
		
		$timestamp = time();

		$comment = sanitizeText(mysql_real_escape_string($comment));

		$query = "INSERT INTO comment (appealID, timestamp, comment, commentUser, action) VALUES (";
		$query .= $this->appealID . ", ";
		$query .= "NOW() , '";
		$query .= $comment . "', ";
		$query .= $firstuserid . ", ";
		$query .= $action . ");";


		$result = mysql_query($query, $db);

		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}

		$id = mysql_insert_id($db);

		//Only update for actual actions
		if ($action == 1) {
			Appeal::getAppealById($this->appealID)->updateLastLogId($id);
		}
	
		$this->log[$this->Count + 1] = new LogItem(array('commentID' => $id, 'appealID' => $this->appealID, 'timestamp' => $timestamp, 'comment' => $comment, 'commentUser' => $seconduserid, 'action' => $action));
		$this->Count++;
	}

	function addAppellantReply($reply){
		$db = connectToDB();

		$reply = sanitizeText(mysql_real_escape_string($reply));

		$timestamp = time();

		$query = "INSERT INTO comment (appealID, timestamp, comment, commentUser) VALUES (";
		$query .= $this->appealID . ", ";
		$query .= "NOW() , '";
		$query .= $reply . "', ";
		$query .= "NULL);";

		$result = mysql_query($query, $db);

		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}

		$id = mysql_insert_id($db);

		$this->log[$this->Count + 1] = new LogItem(array('commentID' => $id, 'appealID' => $this->appealID, 'timestamp' => $timestamp, 'comment' => $reply, 'commentUser' => null, 'action' => null));
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
			$italicsStart = ($data['action']) ? "<i>" : "";
			$italicsEnd = ($data['action']) ? "</i>" : "";
			$username = ($data['commentUser']) ? "<a href=\"userMgmt.php?userId=" . $data['commentUser'] . "\">" . User::getUserById($data['commentUser'])->getUserName() . "</a>" : Appeal::getAppealByID($data['appealID'])->getCommonName();
			// if posted by appellant
			if(!$data['commentUser']){
				$styleUser = "highlight";
				$styleAction = "highlight";
			}
			$HTMLOutput .= "<tr>";
			$HTMLOutput .= "<td class=\"" . $styleUser . "\">" . $username . "</td>";
			if (strlen($data['comment']) > 150) {
				$dots = "...";
			} else {
				$dots = "";
			}
			$HTMLOutput .= "<td class=\"" . $styleAction . "\">" . $italicsStart . substr(sanitizeText(str_replace("\\r\\n", " ", $data['comment'])),0,150) . $italicsEnd . $dots . "</td>";
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
		$HTMLOutput .= "<th class=\"logLargeTimeHeader\">Timestamp</th>";
		$HTMLOutput .= "</tr>";

		for ($i = 0; $i < count($this->log); $i++) {
			$styleUser = ($i%2 == 1) ? "largeLogUserOne" : "largeLogUserTwo";
			$styleAction = ($i%2 == 1) ? "largeLogActionOne" : "largeLogActionTwo";
			$styleTime = ($i%2 == 1) ? "largeLogTimeOne" : "largeLogTimeTwo";
			$data = $this->log[$i]->getLogArray();
			$timestamp = (is_numeric($data['timestamp']) ? date("Y-m-d H:m:s", $data['timestamp']) : $data['timestamp']);
			$username = ($data['commentUser']) ? "<a href=\"userMgmt.php?userId=" . $data['commentUser'] . "\">" . User::getUserById($data['commentUser'])->getUserName() . "</a>" : Appeal::getAppealByID($data['appealID'])->getCommonName();
			$italicsStart = ($data['action']) ? "<i>" : "";
			$italicsEnd = ($data['action']) ? "</i>" : "";
			// if posted by appellant
			if(!$data['commentUser']){
				$styleUser = "highlight";
				$styleAction = "highlight";
				$styleTime = "highlight";
			}
			$comment = $data['comment'];
			// add links to other appeals: UTRS-67
			$comment = preg_replace("~\[\[(\d+)\]\]~", '<a href="' . getRootURL() . 'appeal.php?id=$1">$1</a>', $comment);

			$HTMLOutput .= "<tr>";
			$HTMLOutput .= "<td valign=top class=\"" . $styleUser . "\">" . $username . "</td>";
			$HTMLOutput .= "<td valign=top class=\"" . $styleAction . "\">" . $italicsStart . sanitizeText(str_replace("\\r\\n", "<br>", $comment)) . $italicsEnd . "</td>";
			$HTMLOutput .= "<td valign=top class=\"" . $styleTime . "\">" . $timestamp . "</td>";
			$HTMLOutput .= "</tr>";
		}

		$HTMLOutput .= "</table>";

		return $HTMLOutput;
	}

	//IRC bot notification system
	public static function ircNotification($message, $notify_unblock = 0) {
		$db = connectToDB();

		$query = "INSERT INTO irc (notification, unblock) VALUES ('";
		$query .= mysql_real_escape_string($message) . "', ";
		$query .= $notify_unblock . ");";

		$result = mysql_query($query, $db);

		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}
	}
}

?>