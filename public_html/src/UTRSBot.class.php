<?php

require_once("../includes/Peachy/init.php");

class UTRSBot {
	
	private $username;
	
	private $password;
	
	private $objPeachy;
		
	public function __construct() {
		
		global $CONFIG;
		
		$this->username = $CONFIG["bot"]["username"];
		$this->username = $CONFIG["bot"]["password"];
		
		$objPeachy = Peachy::newWiki( null, $this->username, $this->password, null );
		
	}
	
	public function notifyUser(string $username, string $template, array $templateVars) {
		
		$user = $this->objPeachy->initUser( $username );
		
		if ($user->exists()) {
			
			$page = $objPeachy->initPage( "User_talk:" . $username );
			
			$content = "{{subst:" . $template;
			
			foreach ($templateVars as $var) {
				
				$content = "|" . $var;
				
			}
			
			$content = "}}";
			
			$page->append( $content );
			
		}
		
	}
}