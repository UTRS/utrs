<?php
/**
 * This script is intended to be run from the command line, ideally
 * with a cron job, on a weekly basis. It will search the database for 
 * any closed appeals and delete their associated privacy-related data.
 */

require_once('unblocklib.php');
require_once('exceptions.php');

echo "Starting to clear out private data from closed appeals.\n";

try{

	$db = connectToDB();

	$query = "SELECT appealID, ip FROM appeal WHERE email IS NOT NULL AND status = 'CLOSED'";

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

		$appealIds = array();

		echo "Starting to remove private data...\n";

		for($i = 0; $i < $rows; $i++){
			$appeal = mysql_fetch_assoc($result);
			echo "Processing appeal #" . $appeal . "\n";
			
			$query = "UPDATE appeal SET email = NULL, ip = '" . md5($appeal['ip']) . "' WHERE appealID = '" . $appeal['appealID'] . "'";
			echo "\tRunning query: " . $query . "\n";
			if(!$result){
				throw new UTRSDatabaseException(mysql_error($db));
			}
			// else
			$query = "DELETE FROM cuData WHERE appealID = '" . $appeal['appealID'] . "'";
			echo "\tRunning query: " . $query . "\n";
			$result = mysql_query($query, $db);
			if(!$result){
				throw new UTRSDatabaseException(mysql_error($db));
			}
			echo "Appeal #" . $appeal . " complete.\n";
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