<?php

require_once("includes/Peachy/Init.php");

class UTRSBot {
   
   private $objPeachy;
   private $userTemplate = "Unblock-utrs";
   private $adminTemplate = "Unblock-UTRS-AdminNotify";
   private $oppTemplate = "UTRS-OPP";
      
   public function __construct() {
      
      $this->objPeachy = Peachy::newWiki( "UTRSBot" );
      
   }
   
   public function notifyUser($username, $templateVars) {
      
      $user = $this->objPeachy->initUser( $username );
	  
	  $template = $this->objPeachy->initPage( $this->userTemplate );
	  
	  $this->objPeachy->set_runpage("User:UTRSBot/notifyUser");
      
      if ($user->exists() && $template->exists()) {
         
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
      
	  $template = $this->objPeachy->initPage( $this->userTemplate );
	  
	  $this->objPeachy->set_runpage("User:UTRSBot/notifyAdmin");
      
      if ($user->exists() && $template->exists()) {
         
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
      
	  $template = $this->objPeachy->initPage( $this->oppTemplate );
	  
	  $this->objPeachy->set_runpage("User:UTRSBot/notifyOPP");
      
      if ($template->exists()) {
         
         $page = $this->objPeachy->initPage( "User:UTRSBot/OPP/Requests" );
         
         $content = "\n{{subst:" . $this->oppTemplate;
         
         foreach ($templateVars as $var) {
            
            $content .= "|" . $var;
            
         }
         
         $content .= "}}";
         
         $page->append( $content, "Proxy check requested for UTRS", false, true );
         
      }
   }
}
