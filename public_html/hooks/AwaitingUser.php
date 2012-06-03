<?php

class AwaitingUser
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {

      echo "<h2>Awaiting user response</h2>";
      echo printUserReplyNeeded();



   }

}
