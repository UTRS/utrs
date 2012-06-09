<?php

require_once("src/unblocklib.php");
require_once("src/userObject.php");

verifyLogin('home.php');

$user_id = User::getUserByUsername($_SESSION['user'])->getUserId();

$item = $_POST['item'];

$column = $_POST['column'];

$index = $_POST['index'];

$oldindex = $_POST['oldindex'];

$oldcolumn = $_POST['oldcolumn'];

$db = connectToDB();

$query = $db->prepare("SELECT count(*) FROM hooks WHERE user_id = :userid AND hook_class = :item");

$count = count($query->execute(Array("userid" => $user_id, "item" => $item)));

try {
   if ($count > 0) {
      //Update
      echo "update";
      $query = $db->prepare("UPDATE hooks SET `order` = `order` - 1 WHERE `order` > :oldindex AND user_id = :userid AND zone = :oldcolumn;UPDATE hooks SET `order` = `order` + 1 WHERE `order` >= :index AND zone = :column AND user_id = :userid;UPDATE hooks SET zone = :column, `order` = :index WHERE user_id = :userid AND hook_class = :item;");
      $query->execute(Array(":userid" => $user_id, ":item" => $item, ":column" => $column, ":index" => $index, ":oldindex" => $oldindex, ":oldcolumn" => $oldcolumn));
   } else {
      //Insert
      echo "insert";
      $query = $db->prepare("INSERT INTO hooks VALUES (:userid, :item, :column, :index);");
      $query->execute(Array(":userid" => $user_id, ":item" => $item, ":column" => $column, ":index" => $index));
   }
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
