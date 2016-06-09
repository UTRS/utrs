<?php
require_once('../src/messages.php');
class NewRequests
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
      echo "<h2>".SystemMessages::$system['NewRequestsHook'][$lang]."</h2>";
      echo printNewRequests();
   }

}
