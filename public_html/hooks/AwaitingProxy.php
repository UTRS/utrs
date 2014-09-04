<?php

class AwaitingProxy
{

   private $secure;

   public function __construct() {
      $this->secure = getCurrentUser()->getUseSecure();
   }

   public function getOutput() {
      echo "<h2>Awaiting <a href=\"" . getWikiLink("WP:OPP", $this->secure) . "\" target=\"_new\">WP:OPP</a></h2>";
      echo printProxyCheckNeeded();

   }

}
