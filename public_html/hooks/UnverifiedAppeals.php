<?php
require_once('../src/messages.php');
class UnverifiedAppeals
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	if(verifyAccess($GLOBALS['DEVELOPER'])){
      echo "<h2>".SystemMessages::$system['UnverifiedHook'][$lang]."</h2>";
      echo printUnverified();
   	}
      


   }

}
