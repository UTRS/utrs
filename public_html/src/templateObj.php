<?php

//TODO: Template is not saving line feeds
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('exceptions.php');
require_once('unblocklib.php');
require_once('userObject.php');

class Template{
   private $templateID;
   private $name;
   private $text;
   private $lastEditTime;
   private $lastEditUser;
   private $statusUser;
   private $statusClose;
   
   public function __construct(array $vars, $fromDB){
      
      debug('in constructor for MgmtLog');
      if($fromDB){
         $this->templateID = $vars['templateID'];
         $this->name = $vars['name'];
         $this->text = $vars['text'];
         $this->lastEditTime = $vars['lastEditTime'];
         $this->lastEditUser = WikiUser::getUserById($vars['lastEditUser']);
         if (isset($vars['statusUser']) && $vars['statusUser'] == 1) {
            $this->statusUser = $vars['statusUser'];
         } else {
            $this->statusUser = 0;
         }
         if (isset($vars['statusClose']) && $vars['statusClose'] == 1) {
            $this->statusClose = $vars['statusClose'];
         } else {
            $this->statusClose = 0;
         }
      }
      else{
         $this->name = $vars['name'];
         $this->text = $vars['text'];
         $this->lastEditUser = getCurrentUser();
         if (isset($vars['statusUser']) && $vars['statusUser'] == 1) {
            $this->statusUser = $vars['statusUser'];
         } else {
            $this->statusUser = 0;
         }
         if (isset($vars['statusClose']) && $vars['statusClose'] == 1) {
            $this->statusClose = $vars['statusClose'];
         } else {
            $this->statusClose = 0;
         }
         
         $this->insert();
      }
   }
   
   private function insert(){
      $db = connectToDB(true); // going to take place prior to a potential redirection
      
      $query = $db->prepare("
         INSERT INTO template
         (name, text, lastEditUser, statusUser, statusClose)
         VALUES (:name, :text, :lastEditUser, :statusUser, :statusClose)");

      $result = $query->execute(array(
         ':name'     => $this->name,
         ':text'     => $this->text,
         ':lastEditUser'   => $this->lastEditUser->getUserId(),
         ':statusUser'  => $this->statusUser,
         ':statusClose' => $this->statusClose));
      
      if(!$result){
         $error = var_export($query->errorInfo(), true);
         debug('ERROR: ' . $error . '<br/>');
         throw new UTRSDatabaseException($error);
      }

      $this->templateID = $db->lastInsertId();

      $this->updateLastEditTime($db);
   }
   
   public static function getTemplateById($id){
      $db = connectToDB();
      
      $query = $db->prepare('SELECT * FROM template WHERE templateID = :templateID');

      $result = $query->execute(array(
         ':templateID'  => $id));
      
      if(!$result){
         $error = var_export($query->errorInfo(), true);
         throw new UTRSDatabaseException($error);
      }

      $values = $query->fetch(PDO::FETCH_ASSOC);
      $query->closeCursor();

      if($values === false){
         throw new UTRSDatabaseException('No results were returned for template ID ' . $id);
      }
      
      return new Template($values, true);
   }
   
   public static function getTemplateList() {
      $db = connectToDB();
      
      $query = $db->query("SELECT templateID, name FROM template WHERE templateID != 65 ORDER BY name ASC;");
      
      if($query === false){
         $error = var_export($db->errorInfo(), true);
         throw new UTRSDatabaseException($error);
      }
      
      return $query;
   }
   
   public function getId(){
      return $this->templateID;
   }
   
   public function getName(){
      return $this->name;
   }
   
   public function getText(){
      return $this->text;
   }
   
   public function getLastEditTime(){
      return $this->lastEditTime;
   }
   
   public function getLastEditUser(){
      return $this->lastEditUser;
   }
   
   public function getStatusUser() {
      return $this->statusUser;
   }
   
   public function getStatusClose() {
      return $this->statusClose;
   }
   
   /**
    * The database is set to update the lastEditTime field whenever we update.
    * This function grabs that value after we change something.
    * @param database_reference $db
    */
   private function updateLastEditTime($db){
      $query = $db->prepare("SELECT lastEditTime FROM template WHERE templateID = :templateID");
      
      $result = $query->execute(array(
         ':templateID'  => $this->templateID));
      
      if(!$result){
         $error = var_export($query->errorInfo(), true);
         debug('ERROR: ' . $error . '<br/>');
         throw new UTRSDatabaseException($error);
      }
      
      $data = $query->fetch(PDO::FETCH_ASSOC);
      $query->closeCursor();
      
      $this->lastEditTime = $data['lastEditTime'];
   }
   
   /**
    * When updating both name and text, call me first! Name is subject to a UNIQUE constraint,
    * text is not. Calling it first could leave things in an inconsistent state.
    * 
    * @param string $newName
    * @throws UTRSDatabaseException
    */
   public function setName($newName){
      $user = getCurrentUser();
      
      $db = connectToDB();
      
      $query = $db->prepare("
         UPDATE template
         SET name = :name,
             lastEditUser = :lastEditUser
         WHERE templateID = :templateID");

      $result = $query->execute(array(
         ':name'     => $newName,
         ':lastEditUser'   => $user->getUserId(),
         ':templateID'  => $this->templateID));

      if(!$result){
         $error = var_export($query->errorInfo(), true);
         debug('ERROR: ' . $error . '<br/>');
         throw new UTRSDatabaseException($error);
      }
      
      $this->name = $newName;
      $this->lastEditUser = $user;
      
      $this->updateLastEditTime($db);
   }
   
   /**
    * When updating both name and text, call me last! Name is subject to a UNIQUE constraint,
    * text is not. Calling it first could leave things in an inconsistent state.
    * 
    * @param string $newText
    * @throws UTRSDatabaseException
    */
   public function setText($newText){     
      $user = getCurrentUser();
      
      $db = connectToDB();
      
      $query = $db->prepare("
         UPDATE template
         SET text = :text,
             lastEditUser = :lastEditUser
         WHERE templateID = :templateID");

      $result = $query->execute(array(
         ':text'     => $newText,
         ':lastEditUser'   => $user->getUserId(),
         ':templateID'  => $this->templateID));

      if(!$result){
         $error = var_export($query->errorInfo(), true);
         debug('ERROR: ' . $error . '<br/>');
         throw new UTRSDatabaseException($error);
      }
      
      $this->text = $newText;
      $this->lastEditUser = $user;
      
      $this->updateLastEditTime($db);
   }
   
   public function setStatus($statusUser, $statusClose) {
      $user = getCurrentUser();
      
      $db = connectToDB();
      
      $query = $db->prepare("
         UPDATE template
         SET statusUser = :statusUser,
             statusClose = :statusClose,
             lastEditUser = :lastEditUser
         WHERE templateID = :templateID");

      $result = $query->execute(array(
         ':statusUser'  => $statusUser,
         ':statusClose' => $statusClose,
         ':lastEditUser'   => $user->getUserId(),
         ':templateID'  => $this->templateID));
      
      if(!$result){
         $error = var_export($query->errorInfo(), true);
         debug('ERROR: ' . $error . '<br/>');
         throw new UTRSDatabaseException($error);
      }
      
      $this->statusUser = $statusUser;
      $this->statusClose = $statusClose;
      $this->lastEditUser = $user;
      
      $this->updateLastEditTime($db);
   }
   
   public function delete(){
      $db = connectToDB();
      
      $query = $db->prepare("DELETE FROM template WHERE templateID = :templateID");
      
      $result = $query->execute(array(
         ':templateID'  => $this->templateID));
      
      if(!$result){
         $error = var_export($query->errorInfo(), true);
         debug('ERROR: ' . $error . '<br/>');
         throw new UTRSDatabaseException($error);
      }
      
      $this->templateID = null;
      $this->name = null;
      $this->text = null;
      $this->lastEditTime = null;
      $this->lastEditUser = null;
   }
}

?>
