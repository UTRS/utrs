<?php

class ClosedRequests
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {

      echo "<h2>Last 5 closed requests</h2>";
      echo printRecentClosed();


   }

}
