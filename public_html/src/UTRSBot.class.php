<?php

require_once("includes/Peachy/Init.php");

class UTRSBot {
   
   private $objPeachy;
   private $userTemplate = "Unblock-utrs";
   private $adminTemplate = "Unblock-UTRS-AdminNotify";
      
   public function __construct() {
      
      
      $this->objPeachy = Peachy::newWiki( "UTRSBot" );
      
   }
   
   public function notifyUser($username, $templateVars) {
      
      $user = $this->objPeachy->initUser( $username );
      
      if ($user->exists()) {
         
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
      
      if ($user->exists()) {
         
         $page = $this->objPeachy->initPage( "User_talk:" . $username );
         
         $content = "\n{{subst:" . $this->adminTemplate;
         
         foreach ($templateVars as $var) {
            
            $content .= "|" . $var;
            
         }
         
         $content .= "}}";
         
         $page->append( $content, "Notifing blocking admin for UTRS Appeal", false, true );
         
      }
      
   }
}
