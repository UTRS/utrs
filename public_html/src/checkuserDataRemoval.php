<?php
/**
 * This script is intended to clear out private data on a daily basis.
 */

require_once('unblocklib.php');
require_once('exceptions.php');
require_once(__DIR__ . "/../src/appealObject.php");

echo "Starting to clear out private data from closed appeals.\n";

try{

	$db = connectToDB();
	
	// appeals closed more than six days ago
	$closedAppealsSubquery = "SELECT DISTINCT appealID FROM actionAppealLog WHERE " .
		"comment = 'Closed' AND timestamp < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 6 DAY)";

    // appeals that are unverified for more than 6 days
    $unverifiedAppealsSubquery = "SELECT DISTINCT appealID FROM appeal WHERE status = 'Unverified' " .
                " AND timestamp < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 6 DAY)";
    // appeals that have been marked invalid by a developer
    $invalidAppealsSubquery = "SELECT DISTINCT appealID FROM appeal WHERE " .
        		"status = 'Invalid'";

	// grab appeals and IPs
	$query = "SELECT appealID, ip, email, status FROM appeal WHERE (appealID = ANY (" . $closedAppealsSubquery . ")" .
			        " OR appealID = ANY (" . $unverifiedAppealsSubquery . ")" .
			        "OR appealID = ANY (" . $invalidAppealsSubquery . "))" .	
                                " AND email IS NOT NULL" .
				" AND ip LIKE '%.%.%.%'";
		
	echo "Running query: " . $query . "\n";

	$stmt = $db->query($query);

	if($stmt === false){
		$error = var_export($db->errorInfo(), true);
		throw new UTRSDatabaseException($error);
	}

	$results = $stmt->fetchAll(PDO::FETCH_BOTH);
	$stmt->closeCursor();

	$rows = count($results);

	if($rows == 0){
		echo "There are no recently closed appeals that need data removed.\n";
	}
	else{
		echo "Preparing statements...\n";

		$obscure_appeal_stmt = $db->prepare("
			UPDATE appeal
			SET email = NULL,
			    ip = :ip
			WHERE appealID = :appealID");

		$delete_cudata_stmt = $db->prepare("
			DELETE FROM cuData
			WHERE appealID = :appealID");

                $close_unverify_stmt = $db->prepare("
                        UPDATE appeal SET status = 'CLOSED'
                        WHERE appealID = :appealID");

		echo "Getting appeal IDs from " . $rows . " appeals...\n";

		echo "Starting to remove private data...\n";

		foreach ($results as $appeal) {
			if ($appeal['status'] == Appeal::$STATUS_INVALID) {
				$invalid = TRUE;
			}
			else {
				$invalid = FALSE;
			}
			echo "Processing appeal #" . $appeal['appealID'] . "\n";

			echo "\tObscuring IP address and blanking email address...\n";

			$update = $obscure_appeal_stmt->execute(array(
				':ip'	=> md5($appeal['ip']),
				':appealID'	=> $appeal['appealID']));

			if(!$update){
				$error = var_export($obscure_appeal_stmt->errorInfo(), true);
				throw new UTRSDatabaseException($error);
			}

			echo "\tDeleting CU data...\n";
			$delete = $delete_cudata_stmt->execute(array(
				':appealID'	=> $appeal['appealID']));

			if(!$delete){
				$error = var_export($delete_cudata_stmt->errorInfo(), true);
				throw new UTRSDatabaseException($error);
			}
			if (!$invalid) {
                        echo "\tClosing appeal...\n";
                        $close = $close_unverify_stmt->execute(array(
                                ':appealID'     => $appeal['appealID']));

                        if(!$close){
                                $error = var_export($close_unverify_stmt->errorInfo(), true);
                                throw new UTRSDatabaseException($error);
                        }
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
	
	$body = "O Great Creators,\n\n";
	$body .= "The private data removal script has failed. The error message received was:\n";
	$body .= $e->getMessage() . "\n";
	$body .= "Please check the database to resolve this issue and ensure that private data is removed on schedule.\n\n";
	$body .= "Thanks,\nUTRS";
	$subject = "URGENT: Private data removal failed";
	
	mail('utrs-developers@googlegroups.com', $subject, $body);
}

exit;
?>
