<?php
require_once('../src/messages.php');
class AwaitingToolAdmin
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {

      echo "<h2>".System::$system['AwaitAdminHook'][$lang]."</a></h2>";
      echo printToolAdmin();

   }

}
