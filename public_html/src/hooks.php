<?

$installedHooks = array();
$hooksArray = array();
$hooksArray[1] = array();
$hooksArray[2] = array();
$hooksArray[3] = array();

function init() {
      global $hooksArray;
      global $installedHooks;
      $db = connectToDB();

      $query = $db->prepare("SELECT * FROM hooks WHERE `user_id` = :user_id ORDER BY `zone`, `order`");

      $result = $query->execute(Array(
         ":user_id" => getCurrentUser()->getUserId()));

      if(!$result){
         $error = var_export($query->errorInfo(), true);
         throw new UTRSDatabaseException($error);
      }

      $useDefault = true;

	  while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
	     $hooksArray[$data['zone']][$data['order']] = $data['hook_class'];
	     $useDefault = false;
	  }

	  if ($useDefault) {
      	$hooksArray = unserialize('a:3:{i:1;a:4:{i:0;s:11:"NewRequests";i:1;s:13:"AwaitingProxy";i:2;s:6:"OnHold";i:3;s:14:"ClosedRequests";}i:2;a:6:{i:0;s:15:"CheckUserNeeded";i:1;s:16:"AwaitingReviewer";i:2;s:12:"AwaitingUser";i:3;s:17:"AwaitingToolAdmin";i:4;s:7:"Backlog";i:5;s:17:"UnverifiedAppeals";}i:3;a:2:{i:0;s:7:"MyQueue";i:1;s:11:"WaitingOnMe";}}');
	  }

      $query->closeCursor();



      $db = connectToDB();

      $query = $db->prepare("SELECT data FROM config WHERE `config` = 'installed_hooks';");

      $result = $query->execute();

      if(!$result){
         $error = var_export($query->errorInfo(), true);
         throw new UTRSDatabaseException($error);
      }


	    $values = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();
		$installedHooks = unserialize($values['data']);
}


function getHooks() {

     global $hooksArray;
     global $installedHooks;

     echo "<div id=\"hookContainer\">";
     for ($i = 1; $i <= 3; $i++) {

        echo "<ul valign=\"top\" id=\"Zone" . $i . "\">";

         for ($hook = 0; $hook < count($hooksArray[$i]); $hook++) {
         	if (in_array($hooksArray[$i][$hook], $installedHooks)) {
	            echo "<li id=\"" . $hooksArray[$i][$hook] . "\">";
	            require_once("hooks/" . $hooksArray[$i][$hook] . ".php");
	            $hooksArray[$i][$hook] = new $hooksArray[$i][$hook]();

	            echo $hooksArray[$i][$hook]->getOutput();

	            echo "</li>";
         	}
         }

        echo "</ul>";
     }

     echo "<br style=\"clear: both;\">";
     echo "</div>";
     echo "<div id=\"bottomZoneContainer\">Unused Widgets:<ul id=\"bottomZone\">";

     //print each file name
     foreach($installedHooks as $hook_name)
     {
     	if (strpos(serialize($hooksArray), $hook_name) == FALSE) {
     		echo "<li id=\"" . $hook_name . "\">" . $hook_name . "</li>";
     	}
     }
     echo "</ul></div>";

     //Trash bin
     echo "<div id=\"trashbincontainer\">Trash bin:<ul id=\"trashbin\"></ul></div>";

}


init();
