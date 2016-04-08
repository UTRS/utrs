<?php

class MyStats
{
   private $numAppealsClosed = 0;
   private $numAppealsOpen = 0;
   private $numAppealsWaitingMe = 0;
   private $avgReviewTime = 0;
   private $dateRegistered = 0;
   private $numEmailsSent = 0;
   private $numCommentsMade = 0;
   
   
   public function __construct() {
      buildStats();
   }
   
   public function getOutput() {
      echo "\t<h2>My Statistics</h2>\n";
      echo "\t<table class=\"appealList\">\n";
         echo "\t\t<tr>\n";
            echo "\t\t\t<th>Date Registered:</th>\n";
            echo "\t\t\t<td>" . $dateRegistered . "</td>\n";
         echo "\t\t</tr>\n";
         echo "\t\t<tr>\n";
            echo "\t\t\t<th>Closed Appeals:</th>\n";
            echo "\t\t\t<td>" . $numAppealsClosed . "</td>\n";
         echo "\t\t</tr>\n";
         echo "\t\t<tr>\n";
            echo "\t\t\t<th>Open Appeals:</th>\n";
            echo "\t\t\t<td>" . $numAppealsOpen . "</td>\n";
         echo "\t\t</tr>\n";
         echo "\t\t<tr>\n";
            echo "\t\t\t<th>Waiting on Me:</th>\n";
            echo "\t\t\t<td>" . $numAppealsWaitingMe . "</td>\n";
         echo "\t\t</tr>\n";
         echo "\t\t<tr>\n";
            echo "\t\t\t<th>Average Review Time:</th>\n";
            echo "\t\t\t<td>" . $avgReviewTime . "</td>\n";
         echo "\t\t</tr>\n";
         echo "\t\t<tr>\n";
            echo "\t\t\t<th>Emails Sent:</th>\n";
            echo "\t\t\t<td>" . $numEmailsSent . "</td>\n";
         echo "\t\t</tr>\n";
         echo "\t\t<tr>\n";
            echo "\t\t\t<th>Comments Made:</th>\n";
            echo "\t\t\t<td>" . $numCommentsMade . "</td>\n";
         echo "\t\t</tr>\n";
      echo "\t</table>\n";
   }
   
   function buildStats() {
      //Closed Appeals
      //Open Appeals
      //Waiting On Me
      //Avg Review Time
      //Emails Sent
      //Comments Made
      //Date Registered
      
   }
}
