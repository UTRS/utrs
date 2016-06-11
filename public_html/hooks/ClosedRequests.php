<?php
require_once('src/messages.php');
class ClosedRequests
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
      echo "<h2>".SystemMessages::$system['ClosedRequestsHook'][$lang]."</h2>";
      echo printRecentClosed();


   }

}
