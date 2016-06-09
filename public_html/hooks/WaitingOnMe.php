<?php
require_once('../src/messages.php');
class WaitingOnMe
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
      echo "<h2>".SystemMessages::$system['WaitingOnMeHook'][$lang]."</h2>";
      echo printMyReview();


   }

}
