<?php

class UnverifiedAppeals
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {

      echo "<h2>Awaiting email verification</h2>";
      echo printUnverified();


   }

}
