<?php
$params = array_merge($_GET, $_POST);
$method = $_GET.count() == 0 ? 'Post' : 'Get';
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
		echo("action required");
	}
	
	static function login($user, $pass){
		if (!$user || !pass){
			self::displayHelp();
		} else {
			throw new UTRSException("not yet implemented");
		}
	}
	
	static function get_related($id, $searchby){
		switch ($searchby){
			case 'email':
				$query = "SELECT "
		}
	}
		
}