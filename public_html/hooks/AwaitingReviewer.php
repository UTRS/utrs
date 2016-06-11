<?php
require_once('src/messages.php');
class AwaitingReviewer
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
         echo "<h2>".SystemMessages::$system['AwaitReviewerHook'][$lang]."</a></h2>";
         echo printReviewer();


   }

}
