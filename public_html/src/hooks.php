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

      while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
         $hooksArray[$data['zone']][$data['order']] = $data['hook_class'];
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

}


init();
