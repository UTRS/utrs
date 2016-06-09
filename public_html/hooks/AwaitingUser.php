<?php
require_once('src/messages.php');
class AwaitingUser
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
      echo "<h2>".System::$system['AwaitUserHook'][$lang]."</a></h2>";
      echo printUserReplyNeeded();



   }

}
