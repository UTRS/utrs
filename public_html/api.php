<?php
require_once('src/exceptions.php');
require_once('src/unblocklib.php');

$params = array_merge($_GET, $_POST);
$method = count($_GET) == 0 ? 'Post' : 'Get';
if (!isset($params['action'])){
	Api::displayHelp();
} else {
	//if not logged in, kick back to api.php without parameters, which displays help;
	verifyLogin('api.php');
	
	switch ($params['action']){
		case 'login':
			if ($method == 'Get'){
				Api::displayHelp();
			} else {
				echo Api::login($params['user'], $params['pass']);	
			}
			break;
		
		
		case 'relatedAppeals':
			if (!isset($params['id']) || !isset($params['searchby'])){
				Api::displayHelp();
			} else {
				API::get_related($params['id'], $params['searchby']);
			}
			break;
				
			
		
		default:
			Api::displayHelp();
			break;
	}
			
	
}
class Api{
	static function displayHelp(){
		//TODO: write this out.
		echo("You have tried to call the UTRS api with bad parameters");
	}

	static function writeError($text) {
		echo json_encode(array('error' => $text));
	}
	
	static function login($user, $pass){
		if (!$user || !pass){
			self::displayHelp();
		} else {
			throw new UTRSException("API login not yet implemented. The api is only available for logged in users for now");
		}
	}
	
	static function get_related($id, $searchby){
		$returnarray = array();
		
		$db = connectToDB();
						
		$basequery = "SELECT appeal.appealID, appeal.status, COALESCE(appeal.wikiAccountName, appeal.ip) as blocked, last_comment.timestamp
				      FROM appeal
				      INNER JOIN (
				        SELECT max(timestamp) as timestamp, appealID
				        FROM comment
				        GROUP BY appealID ) as last_comment
				      on appeal.appealID = last_comment.appealID

				      %s

				      ORDER BY last_comment.timestamp DESC";

		$params = array(':appealID' => $id);

		switch ($searchby){
			case 'email':
				$where = "WHERE email = (SELECT email FROM appeal WHERE appealID = :appealID)";
				break;
			case 'account':
				$where = "WHERE wikiAccountName = (SELECT wikiAccountName FROM appeal WHERE appealID = :appealID)";
				break;
			case 'ip':
				$where = "WHERE ip = (SELECT ip FROM appeal WHERE appealID = :appealID)";
				break;
			default:
				self::displayHelp();
				return;
		}

		$query = $db->prepare(sprintf($basequery, $where));

		$sqlresult = $query->execute($params);

		if (!$sqlresult) {
			self::writeError('Database error.');
			return;
		}
		
		$returnarray['results'] = $query->fetchAll(PDO::FETCH_ASSOC);
		
		print json_encode($returnarray);
	}
		
}
