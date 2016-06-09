<?php
require_once('../src/messages.php');
class AwaitingReviewer
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {

         echo "<h2>".System::$system['AwaitReviewerHook'][$lang]."</a></h2>";
         echo printReviewer();


   }

}
