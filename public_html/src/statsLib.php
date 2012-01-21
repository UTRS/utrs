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
			$requests .= "\t\t<td><a style=\"color:blue\" href='" . getWikiLink($wpLink . $identity, $secure) . "'>" . $identity . "</a></td>\n";
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
function printUserReplied() {
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
	$criteria =  array('status' => Appeal::$STATUS_CLOSED);
	return printAppealList($criteria, 5, "timestamp DESC");
}
?>