<?php
require_once('src/exceptions.php');
require_once('src/unblocklib.php');

$params = array_merge($_GET, $_POST);
$method = count($_GET) == 0 ? 'Post' : 'Get';
if (!isset($params['action'])){
	Api::displayHelp();
} else {
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
				      on appeal.appealID = last_comment.appealID \n";
		switch ($searchby){
			case 'email':
				$where = sprintf("WHERE email = (SELECT email FROM appeal WHERE appealID = %d)", mysql_real_escape_string($id));
				break;
			case 'account':
				$where = sprintf("WHERE wikiAccountName = (SELECT wikiAccountName FROM appeal WHERE appealID = %d)", mysql_real_escape_string($id));
				break;
			case 'ip':
				$where = sprintf("WHERE ip = (SELECT ip FROM appeal WHERE appealID = %d)", mysql_real_escape_string($id));
				break;
			default:
				self::displayHelp();
				return;
		}
		$query = $basequery . $where . "\n ORDER BY last_comment.timestamp DESC";
		
		$sqlresult = mysql_query($query, $db);
		
		$returnarray['metadata'] = array( 'num_results' => mysql_num_rows($sqlresult));
		$returnarray['results'] = array();
				
		while($row = mysql_fetch_assoc($sqlresult)){
			$returnarray['results'][] = $row;		
		}
		
		print json_encode($returnarray);
	}
		
}