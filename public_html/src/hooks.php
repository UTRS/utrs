<?

$hooksArray = array();
$hooksArray[1] = array();
$hooksArray[2] = array();
$hooksArray[3] = array();

function init() {
      global $hooksArray;
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
}


function getHooks() {

      global $hooksArray;

     for ($i = 1; $i <= 3; $i++) {

        echo "<ul valign=\"top\" style=\"float: left;\" id=\"Zone" . $i . "\">";

         for ($hook = 0; $hook < count($hooksArray[$i]); $hook++) {
            echo "<li id=\"" . $hooksArray[$i][$hook] . "\">";
            require_once("hooks/" . $hooksArray[$i][$hook] . ".php");
            $hooksArray[$i][$hook] = new $hooksArray[$i][$hook]();

            echo $hooksArray[$i][$hook]->getOutput();

            echo "</li>";
         }

        echo "</ul>";
     }

}


init();
