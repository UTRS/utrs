<?php
require_once('../src/messages.php');
class CheckUserNeeded
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {

      echo "<h2>".SystemMessages::$system['CheckUserNeededHook'][$lang]."</h2>";
      echo printCheckuserNeeded();


   }

}
