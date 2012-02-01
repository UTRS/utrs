<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');
require_once('../src/appealObject.php');
require_once('../src/userObject.php');

/**
 * Get an array containing database rows representing the desired appeals.
 * @param array $criteria an array of strings that is converted to a WHERE clause.
 * {"columnName" => "value", " AND columnName2" => "value2", ... }
 * @param int $limit the maximum number to return
 * @param string $orderby the column name to sort by
 * @return a reference to the result of the query
 */
function queryAppeals(array $criteria = array(), $limit = "", $orderby = ""){
	$db = connectToDB();
	
	$query = "SELECT * FROM appeal";
	$query .= " WHERE";
	//Parse all of the criteria
	foreach($criteria as $item => $value) {
		$query .= " " . $item . "= '" . $value . "'";
	}
	//If there is an order, use it.
	if ($orderby != "") {
		$query .= " ORDER BY " . $orderby;
	}
	//If there is a limit, use it.
	if ($limit != "") {
		$query .= " LIMIT 0," . $limit;
	}
	
	debug($query);
	
	$result = mysql_query($query, $db);
	
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	return $result;
}

/**
* Returns a list in an HTML table
* @param Array $criteria the column and value to filter by
* @param integer $limit optional the number of items to return
* @param String $orderby optional order of the results and direction
*/
function printAppealList(array $criteria = array(), $limit = "", $orderby = "") {
	
	$currentUser = getCurrentUser();
	$secure = $currentUser->getUseSecure();
	
	// get rows from DB. Throws UTRSDatabaseException
	$result = queryAppeals($criteria, $limit, $orderby);
	
	$rows = mysql_num_rows($result);
	
	//If there are no new unblock requests
	if ($rows == 0) {
		$norequests = "<b>No unblock requests in queue</b>";
		return $norequests;
	} else {
		$requests = "<table class=\"appealList\">";
		//Begin formatting the unblock requests
		for ($i=0; $i < $rows; $i++) {
			//Grab the rowset
			$data = mysql_fetch_array($result);
			$appealId = $data['appealID'];
			//Determine how we identify the user.  Use username if possible, IP if not
			if ($data['wikiAccountName'] == NULL) {
				$identity = $data['ip'];
				$wpLink = "Special:Contributions/";
			} else {
				$identity = $data['wikiAccountName'];
				$wpLink = "User:";
			}
			//Determine if it's an odd or even row for formatting
			if ($i % 2) {
				$rowformat = "even";
			} else {
				$rowformat = "odd";
			}
			
			$requests .= "\t<tr class=\"" . $rowformat . "\">\n";
			$requests .= "\t\t<td>" . $appealId . ".</td>\n";
			$requests .= "\t\t<td><a style=\"color:green\" href='appeal.php?id=" . $appealId . "'>Zoom</a></td>\n";
			$requests .= "\t\t<td><a style=\"color:blue\" href='" . getWikiLink($wpLink . $identity, $secure) . "' target='_NEW'>" . $identity . "</a></td>\n";
			$requests .= "\t</tr>\n";
		}
		
		$requests .= "</table>";
		
		return $requests;
	}
}

/**
 * Returns a list of all new appeals
 */
function printNewRequests() {
	$criteria =  array('status' => Appeal::$STATUS_NEW);
	return printAppealList($criteria);
}

/**
 * Return a list of all appeals where the appealer has replied to a question, and is awaiting further review
 */
function printToolAdmin() {
	$criteria =  array('status' => Appeal::$STATUS_AWAITING_ADMIN);
	return printAppealList($criteria);
}

/**
 * Return a list of all appeals where the appealer has replied to a question, and is awaiting further review
 */
function printUserReplyNeeded() {
	$criteria =  array('status' => Appeal::$STATUS_AWAITING_USER);
	return printAppealList($criteria);
}

/**
 * Return a list of all appeals that have been flagged for checkuser attention
 */
function printProxyCheckNeeded() {
	$criteria =  array('status' => Appeal::$STATUS_AWAITING_PROXY);
	return printAppealList($criteria);
}

/**
 * Return a list of all appeals that have been flagged for checkuser attention
 */
function printCheckuserNeeded() {
	$criteria =  array('status' => Appeal::$STATUS_AWAITING_CHECKUSER);
	return printAppealList($criteria);
}

/**
 * Return a list of the last five appeals to be closed
 */
function printRecentClosed() {
	$db = connectToDB();
	
	$currentUser = getCurrentUser();
	$secure = $currentUser->getUseSecure();
	
	$query = "SELECT a.appealID, a.wikiAccountName, a.ip FROM appeal a, actionAppealLog l WHERE a.appealID = l.appealID AND l.comment = 'Closed' AND a.status = 'CLOSED' ORDER BY l.timestamp DESC LIMIT 0,5";
	// get rows from DB. Throws UTRSDatabaseException
	$result = mysql_query($query, $db);
	
	$rows = mysql_num_rows($result);
	
	//If there are no new unblock requests
	if ($rows == 0) {
		$norequests = "<b>No unblock requests in queue</b>";
		return $norequests;
	} else {
		$requests = "<table class=\"appealList\">";
		//Begin formatting the unblock requests
		for ($i=0; $i < $rows; $i++) {
			//Grab the rowset
			$data = mysql_fetch_array($result);
			$appealId = $data['appealID'];
			//Determine how we identify the user.  Use username if possible, IP if not
			if ($data['wikiAccountName'] == NULL) {
				$identity = $data['ip'];
				$wpLink = "Special:Contributions/";
			} else {
				$identity = $data['wikiAccountName'];
				$wpLink = "User:";
			}
			//Determine if it's an odd or even row for formatting
			if ($i % 2) {
				$rowformat = "even";
			} else {
				$rowformat = "odd";
			}
			
			$requests .= "\t<tr class=\"" . $rowformat . "\">\n";
			$requests .= "\t\t<td>" . $appealId . ".</td>\n";
			$requests .= "\t\t<td><a style=\"color:green\" href='appeal.php?id=" . $appealId . "'>Zoom</a></td>\n";
			$requests .= "\t\t<td><a style=\"color:blue\" href='" . getWikiLink($wpLink . $identity, $secure) . "' target='_NEW'>" . $identity . "</a></td>\n";
			$requests .= "\t</tr>\n";
		}
		
		$requests .= "</table>";
		
		return $requests;
	}
}

function printBacklog() {
	$db = connectToDB();
	
	$currentUser = getCurrentUser();
	$secure = $currentUser->getUseSecure();
	
	$query = "SELECT a.appealID, a.wikiAccountName, a.ip, DateDiff(Now(), c.last_action) as since_last_action";
	$query .= " FROM appeal a";
	$query .= " LEFT JOIN (SELECT Max(timestamp) as last_action, appealID";
	$query .= " FROM comment";
	$query .= " WHERE action = 1";
	$query .= " GROUP BY appealID) c";
	$query .= " ON c.appealID = a.appealID";
	$query .= " WHERE DateDiff(Now(), c.last_action) > 7;";
	
	// get rows from DB. Throws UTRSDatabaseException
	$result = mysql_query($query, $db);
	
	$rows = mysql_num_rows($result);
	
	//If there are no new unblock requests
	if ($rows == 0) {
		$norequests = "<b>No unblock requests in queue</b>";
		return $norequests;
	} else {
		$requests = "<table class=\"appealList\">";
		//Begin formatting the unblock requests
		for ($i=0; $i < $rows; $i++) {
			//Grab the rowset
			$data = mysql_fetch_array($result);
			$appealId = $data['appealID'];
			//Determine how we identify the user.  Use username if possible, IP if not
			if ($data['wikiAccountName'] == NULL) {
				$identity = $data['ip'];
				$wpLink = "Special:Contributions/";
			} else {
				$identity = $data['wikiAccountName'];
				$wpLink = "User:";
			}
			//Determine if it's an odd or even row for formatting
			if ($i % 2) {
				$rowformat = "even";
			} else {
				$rowformat = "odd";
			}
				
			$requests .= "\t<tr class=\"" . $rowformat . "\">\n";
			$requests .= "\t\t<td>" . $appealId . ".</td>\n";
			$requests .= "\t\t<td><a style=\"color:green\" href='appeal.php?id=" . $appealId . "'>Zoom</a></td>\n";
			$requests .= "\t\t<td><a style=\"color:blue\" href='" . getWikiLink($wpLink . $identity, $secure) . "' target='_NEW'>" . $identity . "</a></td>\n";
			$requests .= "\t\t<td> " . $data['since_last_action'] . " days since last action</td>\n";
			$requests .= "\t</tr>\n";
		}
	
		$requests .= "</table>";
	
		return $requests;
	}
}

function printReviewer() {
	$criteria = array('status' => Appeal::$STATUS_AWAITING_REVIEWER);
	return printAppealList($criteria);
}

function printOnHold() {
	$criteria = array('status' => Appeal::$STATUS_ON_HOLD);
	return printAppealList($criteria);
}

function printMyQueue() {
	$user = User::getUserByUsername($_SESSION['user']);
	$criteria = array('handlingAdmin' => $user->getUserId(), ' AND status !' => Appeal::$STATUS_CLOSED);
	return printAppealList($criteria);
}


function printMyReview() {
	$user = User::getUserByUsername($_SESSION['user']);
	$criteria = array('handlingAdmin' => $user->getUserId(), ' AND status' => Appeal::$STATUS_AWAITING_REVIEWER);
	return printAppealList($criteria);
}
/**
 * Get an array containing database rows representing the desired users.
 * @param array $criteria an array of strings that is converted to a WHERE clause.
 * {"columnName" => "value", " AND columnName2" => "value2", ... }
 * @param int $limit the maximum number to return
 * @param string $orderby the column name to sort by
 * @return a reference to the result of the query
 */
function queryUsers(array $criteria = array(), $limit = "", $orderby = ""){
	$db = connectToDB();
	
	$query = "SELECT * FROM user";
	$query .= " WHERE";
	//Parse all of the criteria
	foreach($criteria as $item => $value) {
		$query .= " " . $item . " = '" . $value . "'";
	}
	//If there is an order, use it.
	if ($orderby != "") {
		$query .= " ORDER BY " . $orderby;
	}
	//If there is a limit, use it.
	if ($limit != "") {
		$query .= " LIMIT 0," . $limit;
	}
	
	debug($query);
	
	$result = mysql_query($query, $db);
	
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	return $result;
}

function printUserList(array $criteria = array(), $limit = "", $orderBy = ""){
	$currentUser = getCurrentUser();
	$secure = $currentUser->getUseSecure();
	
	$result = queryUsers($criteria, $limit, $orderBy);
	
	$rows = mysql_num_rows($result);
	
	if($rows == 0){
		echo "<b>No users meet this criteria.</b>";
	}
	else{
		$list = "<table class=\"appealList\">";
		//Begin formatting the unblock requests
		for ($i=0; $i < $rows; $i++) {
			//Grab the rowset
			$data = mysql_fetch_array($result);
			$userId = $data['userID'];
			$username = $data['username'];
			$wikiAccount = "User:" . $data['wikiAccount'];
			//Determine if it's an odd or even row for formatting
			if ($i % 2) {
				$rowformat = "even";
			} else {
				$rowformat = "odd";
			}
			
			$list .= "\t<tr class=\"" . $rowformat . "\">\n";
			$list .= "\t\t<td>" . $userId . ".</td>\n";
			$list .= "\t\t<td><a style=\"color:green\" href=\"userMgmt.php?userId=" . $userId . "\">Manage</a></td>\n";
			$list .= "\t\t<td>" . $username . "</td>\n";
			$list .= "\t\t<td><a style=\"color:blue\" href='" . getWikiLink($wikiAccount, $secure) . "' target='_NEW'>" . $wikiAccount . "</a></td>\n";
			$list .= "\t</tr>\n";
		}
		
		$list .= "</table>";
		
		return $list;
	}
}

function printUnapprovedAccounts(){
	return printUserList(array("approved" => "0"), "", "registered ASC");	
}

function printInactiveAccounts(){
	return printUserList(array("approved" => "1", " AND active" => "0"), "", "username ASC");	
}

function printActiveAccounts(){
	return printUserList(array("approved" => "1", " AND active" => "1", " AND toolAdmin" =>  "0"), "", "username ASC");	
}

function printAdmins(){
	return printUserList(array("toolAdmin" => "1", " AND active" => "1"), "", "username ASC");	
}

function printCheckusers(){
	return printUserList(array("checkuser" => "1", " AND active" => "1"), "", "username ASC");		
}

function printDevelopers(){
	return printUserList(array("developer" => "1"), "", "username ASC");		
}

function getNumberAppealsClosedByUser($userId){
	$query = "SELECT COUNT(*) AS numClosed FROM appeal WHERE status = '" . Appeal::$STATUS_CLOSED . 
			 "' AND handlingAdmin = '" . $userId . "'";
	
	$db = connectToDB();
	
	$result = mysql_query($query, $db);
	
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	$data = mysql_fetch_assoc($result);
	
	return $data['numClosed'];
}

function printUserLogs($userId){
	if(!$userId){
		throw new UTRSIllegalArgumentException($userId, "A valid userID", "printUserLogs()");
	}
	
	$db = connectToDB();
	
	$query = "SELECT * FROM userMgmtLog WHERE target='" . $userId . "'";
	
	debug($query);
	
	$result = mysql_query($query, $db);
	
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	$rows = mysql_num_rows($result);
	
	// shouldn't happen, but meh
	if($rows == 0){
		echo "<b>No logs exist for this user.</b>";
	}
	else{
		$target = User::getUserById($userId);
		$list = "<table class=\"appealList\">";
		//Begin formatting the logs
		for ($i=0; $i < $rows; $i++) {
			//Grab the rowset
			$data = mysql_fetch_array($result);
			$doneById = $data['doneBy'];
			$doneBy = User::getUserById($doneById);
			$timestamp = $data['timestamp'];
			$action = $data['action'];
			$reason = $data['reason'];
			//Determine if it's an odd or even row for formatting
			if ($i % 2) {
				$rowformat = "even";
			} else {
				$rowformat = "odd";
			}
			
			$list .= "\t<tr class=\"" . $rowformat . "\">\n";
			$list .= "\t\t<td>" . $timestamp . " UTC</td>\n";
			$list .= "\t\t<td>" . $doneBy->getUsername() . " " . $action . " " . $target->getUsername() . 
						($reason ? " (<i>" . $reason . "</i>)" : "") . "</td>\n";
			$list .= "\t</tr>\n";
		}
		
		$list .= "</table>";
		
		return $list;
	}
}

function printTemplateList(){
	
	$db = connectToDB();
	
	$query = "SELECT templateID, name FROM template";
	
	debug($query);
	
	$result = mysql_query($query, $db);
	
	if(!$result){
		$error = mysql_error($db);
		debug('ERROR: ' . $error . '<br/>');
		throw new UTRSDatabaseException($error);
	}
	
	$rows = mysql_num_rows($result);
	
	// shouldn't happen, but meh
	if($rows == 0){
		echo "<b>No templates currently exist.</b>";
	}
	else{
		$user = getCurrentUser();
		$list = "<table class=\"appealList\">";
		//Begin formatting the logs
		for ($i=0; $i < $rows; $i++) {
			//Grab the rowset
			$data = mysql_fetch_array($result);
			$id = $data['templateID'];
			$name = $data['name'];
			//Determine if it's an odd or even row for formatting
			if ($i % 2) {
				$rowformat = "even";
			} else {
				$rowformat = "odd";
			}
			
			$list .= "\t<tr class=\"" . $rowformat . "\">\n";
			$list .= "\t\t<td>" . $name . "</td>\n";
			$list .= "\t\t<td><a style=\"color:green\" href=\"tempMgmt.php?id=" . $id . "\">";
			if(verifyAccess($GLOBALS['ADMIN'])){
				$list .= "Edit";
			}
			else{
				$list .= "View";
			}
			$list .= "</a></td>\n\t</tr>\n";
		}
		
		$list .= "</table>";
		
		return $list;
	}
}
?>