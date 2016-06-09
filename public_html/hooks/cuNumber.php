<?php
require_once('../src/messages.php');
class cuNumber
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
   	global $lang;
      if(verifyAccess($GLOBALS['DEVELOPER'])){
        echo "<h2>".SystemMessages::$system['cuNumberHook1'][$lang]."</h2>";
        echo $this->getNumOfCuData() . " ".SystemMessages::$system['cuNumberHook2'][$lang]."<br>" ;
        echo SystemMessages::$system['cuNumberHook3'][$lang]."<br>" . $this->getOldestCuData() . "<br>";
        echo "<a href=\"/src/checkuserDataRemoval.php\">".SystemMessages::$system['cuNumberHook4'][$lang]."</a>";
      }
   }

   private function getNumOfCuData(){
    $fullvalue = "";
    $db = connectToDB();
    $query = "select count(*) from cuData";
    debug($query);
    $query = $db->query($query);
    if($query === false){
      $error = var_export($db->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
    }
    while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      foreach ($data as $value) {
        $fullvalue .= $value;
      }
    }
    return $fullvalue;
   }

   private function getOldestCuData() {
    $query = "select appealID from cuData limit 1";
    debug($query);
    $fullvalue = "";
    $db = connectToDB();
    $query = $db->query($query);
    if($query === false){
      $error = var_export($db->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
    }
    while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      foreach ($data as $value) {
        $lastAppealID = $value;
      }
    }

    $query = "select timestamp from appeal where appealID=".$lastAppealID;
    debug($query);
    $query = $db->query($query);
    if($query === false){
      $error = var_export($db->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
    }
    while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      foreach ($data as $value) {
        $fullvalue .= $value;
      }
    }

    $query->closeCursor();
    return $fullvalue;
  }

}
