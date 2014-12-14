<?php

class cuNumber
{

   public function __construct() {
      //No Actions Needed
   }

   public function getOutput() {
      if(verifyAccess($GLOBALS['DEVELOPER'])){
        echo "<h2>Number of appeals with CU data:</h2>";
        echo $this->getNumOfCuData() . " appeals have checkuser data in them.<br>" ;
        echo "Latest appeal with CU data at:<br>" . $this->getOldestCuData() . "<br>";
        echo "<a href=\"/src/checkuserDataRemoval.php\">Run Now</a>";
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
