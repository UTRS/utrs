<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');

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
			$requests .= "\t\t<td style=\"color:green\"><small><a href='requests.php?id=" . $data['appeal_id'] . "'>Zoom</a></small></td>\n";
			$requests .= "\t\t<td style=\"color:blue\"><small><a href='" . $wpLink . $identity . "'>" . $identity . "</a></small></td>\n";
			$requests .= "\t</tr>\n";
		}
		
		$requests .= "</table>";
		
		return $requests;
	}
}
function printNewRequests() {
	$criteria =  array('status' => 'NEW');
	return printAppealList($criteria);
}
function printFlaggedRequests() {
	$criteria =  array('status' => 'AWAITING_ADMIN', 'OR status' => 'AWAITING_PROXY');
	return printAppealList($criteria);
}

function printCheckuserNeeded() {
	$criteria =  array('status' => 'AWAITING_CHECKUSER');
	return printAppealList($criteria);
}

function printRecentClosed() {
	$criteria =  array('status' => 'CLOSED');
	return printAppealList($criteria, 5, "timestamp DESC");
}
?>