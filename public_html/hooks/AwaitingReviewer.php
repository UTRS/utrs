<?php

class AwaitingReviewer
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {

         echo "<h2>Awaiting reviewer response</h2>";
         echo printReviewer();


   }

}
