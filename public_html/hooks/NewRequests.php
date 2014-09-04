<?php

class NewRequests
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
      echo "<h2>New Requests</h2>";
      echo printNewRequests();
   }

}
