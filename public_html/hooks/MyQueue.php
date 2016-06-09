<?php
require_once('src/messages.php');
class MyQueue
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
      echo "<h2>".SystemMessages::$system['MyQueueHook'][$lang]."</h2>";
      echo printMyQueue();


   }

}
