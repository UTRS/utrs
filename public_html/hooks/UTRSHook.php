<?php

class UTRSHook
{

   private $data;

   public function __construct() {
      $this->data = "Test";
   }

   public function getOutput() {
      return $this->data;
   }

}

