<?php
/**
 * This script is intended to clear out private data on a daily basis.
 */

require_once('unblocklib.php');
require_once('exceptions.php');

echo "Starting to clear out private data from closed appeals.\n";

try{

	$db = connectToDB();
	
	// appeals closed more than six days ago
	$closedAppealsSubquery = "SELECT DISTINCT appealID FROM actionAppealLog WHERE " .
		"comment = 'Closed' AND timestamp < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 6 DAY)";

	// grab appeals and IPs
	$query = "SELECT appealID, ip FROM appeal WHERE appealID = ANY (" . $closedAppealsSubquery . ")" .
				" AND email IS NOT NULL" .
				" AND ip LIKE '%.%.%.%'";
		
	echo "Running query: " . $query . "\n";

	$result = mysql_query($query, $db);

	if(!$result){
		throw new UTRSDatabaseException(mysql_error($db));
	}
	$rows = mysql_num_rows($result);
	if($rows == 0){
		echo "There are no recently closed appeals that need data removed.\n";
	}
	else{

		echo "Getting appeal IDs from " . $rows . " appeals...\n";

		echo "Starting to remove private data...\n";

		for($i = 0; $i < $rows; $i++){
			$appeal = mysql_fetch_array($result);
			echo "Processing appeal #" . $appeal['appealID'] . "\n";
			
			$query = "UPDATE appeal SET email = NULL, ip = '" . md5($appeal['ip']) . "' WHERE appealID = '" . $appeal['appealID'] . "'";
			echo "\tRunning query: " . $query . "\n";
			$update = mysql_query($query, $db);
			if(!$update){
				throw new UTRSDatabaseException(mysql_error($db));
			}
			// else
			$query = "DELETE FROM cuData WHERE appealID = '" . $appeal['appealID'] . "'";
			echo "\tRunning query: " . $query . "\n";
			$delete = mysql_query($query, $db);
			if(!$delete){
				throw new UTRSDatabaseException(mysql_error($db));
			}
			echo "Appeal #" . $appeal['appealID'] . " complete.\n";
		}
	}

	echo "Script completed successfully!\n";

}
catch(Exception $e){
	echo "ERROR - Exception thrown!\n";
	echo $e->getMessage() . "\n";
	echo "Script incomplete.\n";
}

exit;
?>