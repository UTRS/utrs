<?php
require_once('../src/messages.php');
class AwaitingProxy {

   private $secure;

   public function __construct() {
      $this->secure = getCurrentUser()->getUseSecure();
   }

   public function getOutput() {
      echo "<h2>".System::$system['AwaitProxyHook'][$lang]."</a></h2>";
      echo printProxyCheckNeeded();

   }

}
