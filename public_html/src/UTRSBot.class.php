<?php

require_once("includes/Peachy/Init.php");

class UTRSBot {
   
   private $objPeachy;
   private $taskname			= "UTRS";
   private $config				= "UTRSBot";
   private $userTemplate		= "Unblock-utrs";
   private $adminTemplate		= "Unblock-UTRS-AdminNotify";
   private $oppTemplate			= "UTRS-OPP";
   private $oppPage				= "User:UTRSBot/OPP/Requests";
      
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
         
         $content = "\n{{subst:" . $this->userTemplate;
         
         foreach ($templateVars as $var) {
            
            $content .= "|" . $var;
            
         }
         
         $content .= "}}";
         
         $page->append( $content, "User has submitted an unblock appeal on UTRS", false, true );
         
      }
      
   }
   
   public function notifyAdmin($username, $templateVars) {
      
      $user = $this->objPeachy->initUser( $username );
      
	  $template = $this->objPeachy->initPage( "Template:" . $this->userTemplate );
	  
	  $this->objPeachy->set_runpage("User:UTRSBot/notifyAdmin2");
      
      if ($user->exists() && $template->get_exists()) {
         
         $page = $this->objPeachy->initPage( "User_talk:" . $username );
         
         $content = "\n{{subst:" . $this->adminTemplate;
         
         foreach ($templateVars as $var) {
            
            $content .= "|" . $var;
            
         }
         
         $content .= "}}";
         
         $page->append( $content, "Notifing blocking admin for UTRS Appeal", false, true );
         
      }
   }
   
   public function notifyOPP($ip, $templateVars) {
      
	  $template = $this->objPeachy->initPage( "Template:" . $this->oppTemplate );
	  
	  $this->objPeachy->set_runpage("User:UTRSBot/notifyOPP");
      
      if ($template->get_exists()) {
		  
         $page = $this->objPeachy->initPage( $this->oppPage );
         
         $content = "\n{{subst:" . $this->oppTemplate;
         
         foreach ($templateVars as $var) {
            
            $content .= "|" . $var;
            
         }
         
         $content .= "}}";
         
         $page->append( $content, "Proxy check requested for UTRS", false, true );
      }
   }
}
