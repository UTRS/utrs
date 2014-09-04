<?php

class AwaitingToolAdmin
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {

      echo "<h2>Awaiting tool admin</h2>";
      echo printToolAdmin();

   }

}
