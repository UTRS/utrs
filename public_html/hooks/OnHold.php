<?php
require_once('src/messages.php');
class OnHold
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
      echo "<h2>".SystemMessages::$system['OnHoldHook'][$lang]."</h2>";
      echo printOnHold();

   }

}
