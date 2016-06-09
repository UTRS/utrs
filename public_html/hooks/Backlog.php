<?php
require_once('../src/messages.php');
class Backlog
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
      echo "<h2>".SystemMessages::$system['BacklogHook'][$lang]."</h2>";
      echo printBacklog();


   }

}
