<?php
require_once('src/messages.php');
class AwaitingToolAdmin
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
      echo "<h2>".SystemMessages::$system['AwaitAdminHook'][$lang]."</a></h2>";
      echo printToolAdmin();

   }

}
