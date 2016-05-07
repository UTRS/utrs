<?php

require_once("includes/Peachy/Init.php");

class UTRSBot {
   
   private $objPeachy;
   private $taskname			= "UTRS";
   private $config				= "UTRSBot";
   private $userTemplate		= "UTRS-unblock-user";
   private $adminTemplate		= "UTRS-unblock-admin";
   private $oppTemplate			= "UTRS-OPP";
   private $oppPage				= "Wikipedia:WikiProject_on_open_proxies/Requests";
      
   public function __construct() {
      
      $this->objPeachy = Peachy::newWiki( $this->config );
	  
	  $this->objPeachy->set_taskname( $this->taskname );
      
   }
   
   public function notifyUser($username, $templateVars) {
      
      $user = $this->objPeachy->initUser( $username );
	  
	  $template = $this->objPeachy->initPage( "Template:" . $this->userTemplate );
	  
	  $this->objPeachy->set_runpage("User:UTRSBot/notifyUser");
      
      if ($user->exists() && $template->get_exists()) {
         
         $page = $this->objPeachy->initPage( "User_talk:" . $username );
         
         $content = "\n{{" . $this->userTemplate;
         
         foreach ($templateVars as $var) {
            
            $content .= "|" . $var;
            
         }
         
         $content .= "}}--~~~~";
         
         $page->append( $content, "User has submitted an unblock appeal on UTRS", false, true );
         
      }
      
   }
   
   public function notifyAdmin($username, $templateVars) {
      
	  $user			= $this->objPeachy->initUser( $username );
	  
	  //Get Blocking Admin from API
	  $blockinfo	= $user->get_blockinfo();
      $admin		= $this->objPeachy->initUser( $blockinfo['by'] );
	  $template 	= $this->objPeachy->initPage( "Template:" . $this->adminTemplate );
	  
	  $this->objPeachy->set_runpage("User:UTRSBot/notifyAdmin");
      
      if ($admin->exists() && $template->get_exists()) {
         
         $page 		= $this->objPeachy->initPage( "User_talk:" . $blockinfo['by'] );
         
         $content 	= "\n{{" . $this->adminTemplate;
         
         foreach ($templateVars as $var) {
            
            $content .= "|" . $var;
            
         }
         
         $content .= "}}--~~~~";
         
         $page->append( $content, "Notifing blocking admin for [[User:" . $username . "|" . $username . "]]'s UTRS Appeal #" . $templateVars[0], false, true );
         
      }
   }
   
   public function notifyOPP($ip, $templateVars) {
      
	  $template = $this->objPeachy->initPage( "Template:" . $this->oppTemplate );
	  
	  $this->objPeachy->set_runpage("User:UTRSBot/notifyOPP");
      
      if ($template->get_exists()) {
		  
         $page = $this->objPeachy->initPage( $this->oppPage );
         
         $content = "\n{{" . $this->oppTemplate;
         
         foreach ($templateVars as $var) {
            
            $content .= "|" . $var;
            
         }
         
         $content .= "}}";
         
         $page->append( $content, "Proxy check requested for UTRS", false, true );
      }
   }
}
