<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('./exceptions.php');
require_once('./unblocklib.php');
require_once('./appealObject.php');

/**
* Returns a list in an HTML table
* @param Array $criteria the column and value to filter by
* @param integer $limit optional the number of items to return
* @param String $orderby optional order of the results and direction
*/
function printAppealList(array $criteria = array(), $limit = "", $orderby = "") {
	
	
	$db = connectToDB();
	
	$query = "SELECT appealID, ip, wikiAccountName, timestamp FROM appeal";
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
	
	$rows = mysql_num_rows($result);
	
	//If there are no new unblock requests
	if ($rows == 0) {
		$norequests = "<b>No unblock requests in queue</b>";
		return $norequests;
	} else {
		$requests = "<table cellspacing=\"0\">";
		//Begin formatting the unblock requests
		for ($i=0; $i < $rows; $i++) {
			//Grab the rowset
			$data = mysql_fetch_array($result);
			//Determine how we identify the user.  Use username if possible, IP if not
			if ($data['wikiAccountName'] == NULL) {
				$identity = $data['ip'];
				$wpLink = "http://en.wikipedia.org/wiki/Special:Contributions/";
			} else {
				$identity = $data['wikiAccountName'];
				$wpLink = "http://en.wikipedia.org/wiki/User:";
			}
			//Determine if it's an odd or even row for formatting
			if ($i % 2) {
				$rowformat = "even";
			} else {
				$rowformat = "odd";
			}
			
			$requests .= "\t<tr class=\"" . $rowformat . "\">\n";
			$requests .= "\t\t<td><small><a style=\"color:green\" href='appeal.php?id=" . $data['appealID'] . "'>Zoom</a></small></td>\n";
			$requests .= "\t\t<td><small><a style=\"color:blue\" href='" . $wpLink . $identity . "'>" . $identity . "</a></small></td>\n";
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
function printProxyNeededNeeded() {
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