<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('exceptions.php');
require_once('unblocklib.php');
require_once('appealObject.php');
require_once('userObject.php');

/**
 * Get an array containing database rows representing the desired appeals.
 * @param array $criteria an array of strings that is converted to a WHERE clause.
 * {"columnName" => "value", " AND columnName2" => "value2", ... }
 * @param int $limit the maximum number to return
 * @param string $orderby the column name to sort by
 * @return a reference to the result of the query
 */
function queryAppeals(array $criteria = array(), $limit = "", $orderby = "", $timestamp = 0){
   $db = connectToDB();
   
   if ($timestamp == 0) {
      $query = "SELECT " . Appeal::getColumnsForSelect() . " FROM appeal";
   } else {
      $query = "SELECT " . Appeal::getColumnsForSelect() . ", l.timestamp FROM appeal,";
      $query .= " (SELECT appealID, MAX(timestamp) as timestamp FROM comment GROUP BY appealID) AS l";
   }
   $query .= " WHERE";
   //Parse all of the criteria
   foreach($criteria as $item => $value) {
      $query .= " " . $item . "= '" . $value . "'";
   }
   if ($timestamp == 1) {
      $query .= "AND appeal.appealID = l.appealID";
   }
   //If there is an order, use it.
   if ($orderby != "") {
      $query .= " ORDER BY " . $orderby;
   }
   //If there is a limit, use it.
   if ($limit != "") {
      $query .= " LIMIT 0," . $limit;
   }
   
   debug($query);

   $query = $db->query($query);
   
   if($query === false){
      $error = var_export($db->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
   }
   
   return $query;
}

function printCUData(){
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
    
    $fullvalue.= " appeals have checkuser data in them.<br>" ;
    $fullvalue.= "Latest appeal with CU data at:<br>";
    
    $query = "select appealID from cuData limit 1";
    debug($query);
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

/**
* Returns a list in an HTML table
* @param Array $criteria the column and value to filter by
* @param integer $limit optional the number of items to return
* @param String $orderby optional order of the results and direction
*/
function printAppealList(array $criteria = array(), $limit = "", $orderby = "", $timestamp = 0) {
   
   $currentUser = getCurrentUser();
   $secure = $currentUser->getUseSecure();
   
   // get rows from DB. Throws UTRSDatabaseException
   $query = queryAppeals($criteria, $limit, $orderby, $timestamp);
   
   $requests = "<table class=\"appealList\">";
   $foundone = false;

   //Begin formatting the unblock requests
   while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      $foundone = true;

      $appeal = Appeal::newTrusted($data);
      
      $requests .= "\t<tr>\n";
      $requests .= "\t\t<td>" . $appeal->getID() . ".</td>\n";
      $requests .= "\t\t<td><a style=\"color:green\" href='appeal.php?id=" . $appeal->getID() . "'>Zoom</a></td>\n";
      $requests .= "\t\t<td><a style=\"color:blue\" href='" . getWikiLink("user:".$appeal->getCommonName(), $secure) . "' target='_NEW'>" . $appeal->getCommonName() . "</a></td>\n";
      if ($timestamp == 1) {
         $requests .= "\t\t<td>" . $data['timestamp'] . "</td>\n";
      }
      $requests .= "\t</tr>\n";
   }

   $query->closeCursor();

   if (!$foundone) {
      return "<b>No unblock requests in queue</b>";
   }

   return $requests . "</table>";
}

/**
 * Returns a list of all new appeals
 */
function printNewRequests() {
   $criteria =  array('status' => Appeal::$STATUS_NEW);
   return printAppealList($criteria);
}

/**
 * Return a list of all appeals where the appealer has replied to a question, and is awaiting further review
 */
function printToolAdmin() {
   $criteria =  array('status' => Appeal::$STATUS_AWAITING_ADMIN);
   return printAppealList($criteria);
}

/**
 * Return a list of all appeals where the appealer has replied to a question, and is awaiting further review
 */
function printUserReplyNeeded() {
   $criteria =  array('status' => Appeal::$STATUS_AWAITING_USER);
   return printAppealList($criteria);
}

/**
 * Return a list of all appeals that have been flagged for checkuser attention
 */
function printProxyCheckNeeded() {
   $criteria =  array('status' => Appeal::$STATUS_AWAITING_PROXY);
   return printAppealList($criteria);
}

/**
 * Return a list of all appeals that have been flagged for checkuser attention
 */
function printCheckuserNeeded() {
   $criteria =  array('status' => Appeal::$STATUS_AWAITING_CHECKUSER);
   return printAppealList($criteria);
}

/**
 * Return a list of the last five appeals to be closed
 */
function printRecentClosed() {
   $db = connectToDB();
   
   $currentUser = getCurrentUser();
   $secure = $currentUser->getUseSecure();
   
   /*
   $query = "SELECT a.appealID, a.wikiAccountName, a.ip, l.timestamp";
   $query .= " FROM appeal a,";
   $query .= " (SELECT appealID, MAX(timestamp) as timestamp";
   $query .= " FROM actionAppealLog";
   $query .= " WHERE comment = 'Closed'";
   $query .= " GROUP BY appealID) l";
   $query .= " WHERE l.appealID = a.appealID";
   $query .= " AND a.status = 'CLOSED'";
   $query .= " ORDER BY l.timestamp DESC LIMIT 0,5";
   */
   
   $query = $db->query("
      SELECT " . Appeal::getColumnsForSelect('a') . ", c.timestamp
      FROM appeal AS a, comment AS c
      WHERE a.lastLogId = c.commentID
        AND c.comment = 'Closed'
      ORDER BY c.timestamp DESC LIMIT 0,5;");

   $requests = "<table class=\"appealList\">";
   $foundone = false;

   //Begin formatting the unblock requests
   while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      $foundone = true;
      
      $appeal = Appeal::newTrusted($data);
               
      $requests .= "\t<tr>\n";
      $requests .= "\t\t<td>" . $appeal->getID() . ".</td>\n";
      $requests .= "\t\t<td><a style=\"color:green\" href='appeal.php?id=" . $appeal->getID() . "'>Zoom</a></td>\n";
      $requests .= "\t\t<td><a style=\"color:blue\" href='" . getWikiLink("user:" . $appeal->getCommonName(), $secure) . "' target='_NEW'>" . $appeal->getCommonName() . "</a></td>\n";
      $requests .= "\t\t<td>" . $data['timestamp'] . "</td>\n";
      $requests .= "\t</tr>\n";
   }

   $query->closeCursor();

   if (!$foundone) {
      return "<b>No unblock requests in queue</b>";
   }

   return $requests . "</table>";
}

function printBacklog() {
   $db = connectToDB();
   
   $currentUser = getCurrentUser();
   $secure = $currentUser->getUseSecure();
   
   /*
   $query = "SELECT DISTINCT a.appealID, a.wikiAccountName, a.ip, DateDiff(Now(), cc.last_action) as since_last_action";
   $query .= " FROM appeal a LEFT JOIN";
   $query .= " (SELECT timestamp, appealID, comment";
   $query .= " FROM comment";
   $query .= " WHERE action = 1 ORDER BY commentID DESC) c";
   $query .= " ON c.appealID = a.appealID";
   $query .= " LEFT JOIN (SELECT appealID, Max(timestamp) as last_action";
   $query .= " FROM comment";
   $query .= " WHERE action = 1";
   $query .= " GROUP BY appealID) cc";
   $query .= " ON cc.appealID = c.appealID";
   $query .= " WHERE DateDiff(Now(), cc.last_action) > 7";
        $query .= " AND a.status != 'CLOSED' ";
   $query .= " AND cc.last_action = c.timestamp";
   $query .= " AND c.comment != 'Closed'";
   $query .= " ORDER BY last_action ASC;";
   */
   
   $query = $db->query("
      SELECT DISTINCT " . Appeal::getColumnsForSelect('a') . ", DateDiff(Now(), c.timestamp) AS since_last_action
      FROM appeal AS a, comment AS c
      WHERE a.lastLogId = c.commentID
        AND c.comment != 'Closed'
                  AND a.status != 'UNVERIFIED'
                  AND a.status != 'CLOSED'
        AND DateDiff(Now(), c.timestamp) > 7
      ORDER BY c.timestamp ASC");
   
   $requests = "<table class=\"appealList\">";
   $foundone = false;
   
   //Begin formatting the unblock requests
   while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      $foundone = true;

      $appeal = Appeal::newTrusted($data);

      $requests .= "\t<tr>\n";
      $requests .= "\t\t<td>" . $appeal->getID() . ".</td>\n";
      $requests .= "\t\t<td><a style=\"color:green\" href='appeal.php?id=" . $appeal->getID(). "'>Zoom</a></td>\n";
      $requests .= "\t\t<td><a style=\"color:blue\" href='" . getWikiLink("user:" .$appeal->getCommonName(), $secure) . "' target='_NEW'>" . $appeal->getCommonName() . "</a></td>\n";
      $requests .= "\t\t<td> " . $data['since_last_action'] . " days since last action</td>\n";
      $requests .= "\t</tr>\n";
   }

   $query->closeCursor();

   if (!$foundone) {
      return "<b>No unblock requests in queue</b>";
   }

   return $requests . "</table>";
}

function printUnverified() {
   $criteria = array('status' => Appeal::$STATUS_UNVERIFIED);
   return printAppealList($criteria);
}

function printReviewer() {
   $criteria = array('status' => Appeal::$STATUS_AWAITING_REVIEWER);
   return printAppealList($criteria);
}

function printOnHold() {
   $criteria = array('status' => Appeal::$STATUS_ON_HOLD);
   return printAppealList($criteria);
}

function printMyQueue() {
   $user = User::getUserByUsername($_SESSION['user']);
   $criteria = array('handlingAdmin' => $user->getUserId(), ' AND status !' => Appeal::$STATUS_CLOSED);
   return printAppealList($criteria, "", "", 1);
}

function printAssigned($userId) {
   $user = User::getUserById($userId);
   $criteria = array('handlingAdmin' => $user->getUserId(), ' AND status !' => Appeal::$STATUS_CLOSED);
   return printAppealList($criteria, "", "", 1);
}

function printClosed($userId) {
   $user = User::getUserById($userId);
   $criteria = array('handlingAdmin' => $user->getUserId(), ' AND status ' => Appeal::$STATUS_CLOSED);
   return printAppealList($criteria, "", "", 1);
}


function printMyReview() {
   $user = User::getUserByUsername($_SESSION['user']);
   $criteria = array('handlingAdmin' => $user->getUserId(), ' AND status' => Appeal::$STATUS_AWAITING_REVIEWER);
   return printAppealList($criteria, "", "", 1);
}
/**
 * Get an array containing database rows representing the desired users.
 * @param array $criteria an array of strings that is converted to a WHERE clause.
 * {"columnName" => "value", " AND columnName2" => "value2", ... }
 * @param int $limit the maximum number to return
 * @param string $orderby the column name to sort by
 * @return a reference to the result of the query
 */
function queryUsers(array $criteria = array(), $limit = "", $orderby = ""){
   $db = connectToDB();
   
   $query = "SELECT * FROM user";
   
   $query .= " WHERE";
   //Parse all of the criteria
   foreach($criteria as $item => $value) {
      $query .= " " . $item . " = '" . $value . "'";
   }
   
   //If there is an order, use it.
   if ($orderby != "") {
      $query .= " ORDER BY " . $orderby;
   }
   //If there is a limit, use it.
   if ($limit != "") {
      $query .= " LIMIT 0," . $limit;
   }
   
   debug($query);
   
   $result = $db->query($query);
   
   if($result === false){
      $error = var_export($db->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
   }
   
   return $result;
}

function getPermsDB() {
   global $wikiPerms;
   $users = "";
   
   $result = queryUsers(array("approved" => 1));
   while (($data = $result->fetch(PDO::FETCH_ASSOC)) !== false) {
      $users .= $data['wikiAccount'] . "|";
   }
   $perms_array = explode("|", $users);
   for ($i = 0; $i < count($perms_array); $i = $i + 5) {
      $users = implode("|", array_slice($perms_array, $i, 5));
      $users = str_replace(" ", "_", $users);
      $handle = fopen("https://en.wikipedia.org/w/api.php?action=query&format=php&list=users&ususers=" . $users . "&usprop=groups", "r");
      $read = fread($handle, "4096");
      $Perms = unserialize($read);
      if (is_array($wikiPerms)) {
         $wikiPerms = array_merge_recursive($wikiPerms, $Perms);
      } else {
         $wikiPerms = $Perms;
      }
   }
}

function checkWikiPerms($wikiUserName, $wikiPermission) {
   global $wikiPerms;
   foreach ($wikiPerms["query"]["users"] as $user) {
      if ($user['name'] == ucfirst($wikiUserName)) {
         if (in_array($wikiPermission, $user['groups'])) {
            return true;
         } else {
            return false;
         }
         break;
      }
   }
}

function printUserList(array $criteria = array(), $limit = "", $orderBy = ""){
   $currentUser = getCurrentUser();
   $secure = $currentUser->getUseSecure();
   
   $result = queryUsers($criteria, $limit, $orderBy);
   
   $list = "<table class=\"appealList\">";
   $foundone = false;
   
   //Begin formatting the unblock requests
   while (($data = $result->fetch(PDO::FETCH_ASSOC)) !== false) {
      $foundone = true;

      $userId = $data['userID'];
      $username = $data['username'];
      $wikiAccount = $data['wikiAccount'];
               
      $list .= "\t<tr>\n";
      $list .= "\t\t<td>" . $userId . ".</td>\n";
      $list .= "\t\t<td><a style=\"color:green\" href=\"userMgmt.php?userId=" . $userId . "\">Manage</a></td>\n";
      $list .= "\t\t<td>" . $username . "</td>\n";
      $list .= "\t\t<td><a style=\"color:blue\" href='" . getWikiLink("User:" . $wikiAccount, $secure) . "' target='_NEW'>" . $wikiAccount . "</a></td>\n";
      if (isset($_GET['checkperms']) && $_GET['checkperms'] == "yes") {
         if (array_key_exists("approved", $criteria)) {
            $verified = (checkWikiPerms($wikiAccount, "sysop")) ? "<p style=\"color:green\">Verified</p>" : "<p style=\"color:red\">Unverified</p>";
            $list .= "\t\t<td>" . $verified . "</td>\n";
         } else if (array_key_exists("checkuser", $criteria)) {
            $verified = (checkWikiPerms($wikiAccount, "checkuser")) ? "<p style=\"color:green\">Verified</p>" : "<p style=\"color:red\">Unverified</p>";
            $list .= "\t\t<td>" . $verified . "</td>\n";
         }
      }
      $list .= "\t</tr>\n";
   }

   $result->closeCursor();

   if (!$foundone) {
      return "<b>No users meet this criteria.</b>";
   }

   return $list . "</table>";
}

function printUnapprovedAccounts(){
   return printUserList(array("approved" => "0"), "", "registered ASC");   
}

function printInactiveAccounts(){
   return printUserList(array("approved" => "1", " AND active" => "0"), "", "username ASC"); 
}

function printActiveAccounts(){
   return printUserList(array("approved" => "1", " AND active" => "1", " AND toolAdmin" =>  "0"), "", "username ASC");  
}

function printAdmins(){
   return printUserList(array("toolAdmin" => "1", " AND active" => "1"), "", "username ASC");   
}

function printCheckusers(){
   return printUserList(array("checkuser" => "1", " AND active" => "1"), "", "username ASC");      
}

function printDevelopers(){
   return printUserList(array("developer" => "1"), "", "username ASC");    
}

function getNumberAppealsClosedByUser($userId){
   $db = connectToDB();

   $query = $db->prepare("
      SELECT COUNT(*) AS numClosed
      FROM appeal
      WHERE status = :status
        AND handlingAdmin = :handlingAdmin");

   $result = $query->execute(array(
      ':status'      => Appeal::$STATUS_CLOSED,
      ':handlingAdmin'  => $userId));
   
   if(!$result){
      $error = var_export($query->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
   }
   
   $count = $query->fetchColumn();
   $query->closeCursor();
   
   return $count;
}

function printUserLogs($userId){
   if(!$userId){
      throw new UTRSIllegalArgumentException($userId, "A valid userID", "printUserLogs()");
   }
   
   $db = connectToDB();
   
   $query = $db->prepare("SELECT * FROM userMgmtLog WHERE target = :target");
   
   $result = $query->execute(array(
      ':target'   => $userId));
   
   if(!$result){
      $error = var_export($query->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
   }
   
   $target = User::getUserById($userId);

   $list = "<table class=\"appealList\">";
   $foundone = false;
   
   //Begin formatting the logs
   while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      $foundone = true;

      $doneById = $data['doneBy'];
      $doneBy = User::getUserById($doneById);
      $timestamp = $data['timestamp'];
      $action = $data['action'];
      $reason = $data['reason'];
      $hideTarget = $data['hideTarget'];
               
      $list .= "\t<tr>\n";
      $list .= "\t\t<td>" . $timestamp . " UTC</td>\n";
      $list .= "\t\t<td>" . $doneBy->getUsername() . " " . $action . ($hideTarget ? "" : " " . $target->getUsername()) . 
               ($reason ? " (<i>" . $reason . "</i>)" : "") . "</td>\n";
      $list .= "\t</tr>\n";
   }

   $query->closeCursor();

   // shouldn't happen, but meh
   if (!$foundone) {
      return "<b>No logs exist for this user.</b>";
   }

   return $list . "</table>";
}

function printTemplateList(){
   $db = connectToDB();
   
   $query = $db->query("SELECT templateID, name FROM template ORDER BY name ASC");
   
   if($query === false){
      $error = var_export($db->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
   }
   
   $user = getCurrentUser();

   $list = "<table class=\"appealList\">";
   $foundone = false;
   
   //Begin formatting the logs
   while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      $foundone = true;

      $id = $data['templateID'];
      $name = $data['name'];
               
      $list .= "\t<tr>\n";
      $list .= "\t\t<td>" . $name . "</td>\n";
      $list .= "\t\t<td><a style=\"color:green\" href=\"tempMgmt.php?id=" . $id . "\">";
      if(verifyAccess($GLOBALS['ADMIN'])){
         $list .= "Edit";
      }
      else{
         $list .= "View";
      }
      $list .= "</a></td>\n\t</tr>\n";
   }

   $query->closeCursor();

   if (!$foundone) {
      return "<b>No templates currently exist.</b>";
   }

   return $list . "</table>";
}

function printLastThirtyActions() {
   $db = connectToDB();
   
   $query = $db->query("SELECT * FROM comment WHERE action = 1 ORDER BY timestamp DESC LIMIT 0,30;");
   
   if($query === false){
      $error = var_export($db->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
   }
   
   $HTMLOutput = "";

   $HTMLOutput .= "<table class=\"logLargeTable\">";
   $HTMLOutput .= "<tr>";
   $HTMLOutput .= "<th class=\"logLargeUserHeader\">Appeal</th>";
   $HTMLOutput .= "<th class=\"logLargeUserHeader\">User</th>";
   $HTMLOutput .= "<th class=\"logLargeActionHeader\">Action</th>";
   $HTMLOutput .= "<th class=\"logLargeTimeHeader\">Timestamp</th>";
   $HTMLOutput .= "</tr>";

   $i = 0;

   while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      $styleUser = ($i%2 == 1) ? "largeLogUserOne" : "largeLogUserTwo";
      $styleAction = ($i%2 == 1) ? "largeLogActionOne" : "largeLogActionTwo";
      $styleTime = ($i%2 == 1) ? "largeLogTimeOne" : "largeLogTimeTwo";

      $timestamp = (is_numeric($data['timestamp']) ? date("Y-m-d H:m:s", $data['timestamp']) : $data['timestamp']);
      if ($data['commentUser']) {
         $user = User::getUserById($data['commentUser']);
         $username = "<a href=\"userMgmt.php?userId=" . $user->getUserId() . "\">" . $user->getUsername() . "</a>";
      } else {
         $username = Appeal::getAppealByID($data['appealID'])->getCommonName();
      }
      $italicsStart = ($data['action']) ? "<i>" : "";
      $italicsEnd = ($data['action']) ? "</i>" : "";
      $appeal = "<a href=\"appeal.php?id=" . $data['appealID'] . "\">" . Appeal::getAppealById($data['appealID'])->getCommonName() . "</a>";
      // if posted by appellant
      if(!$data['commentUser']){
         $styleUser = "highlight";
         $styleAction = "highlight";
         $styleTime = "highlight";
      }
      $HTMLOutput .= "<tr>";
      $HTMLOutput .= "<td valign=top class=\"" . $styleUser . "\">" . $appeal . "</td>";
      $HTMLOutput .= "<td valign=top class=\"" . $styleUser . "\">" . $username . "</td>";
      $HTMLOutput .= "<td valign=top class=\"" . $styleAction . "\">" . $italicsStart . str_replace("\\r\\n", "<br>", $data['comment']) . $italicsEnd . "</td>";
      $HTMLOutput .= "<td valign=top class=\"" . $styleUser . "\">" . $timestamp . "</td>";
      $HTMLOutput .= "</tr>";

      $i++;
   }

   $query->closeCursor();

   $HTMLOutput .= "</table>";
   
   return $HTMLOutput;
}

function printSitenoticeMessages(){
   $db = connectToDB();
   
   $query = $db->query("
      SELECT
          messageID,
          LEFT(message, 64) AS summary,
          CHAR_LENGTH(message) AS length
      FROM sitenotice
      ORDER BY messageID ASC");
   
   if($query === false){
      $error = var_export($db->errorInfo(), true);
      debug('ERROR: ' . $error . '<br/>');
      throw new UTRSDatabaseException($error);
   }
   
   $foundone = false;
   
   $table = "<table class=\"sitenoticeTable\">\n";
   $table .= "<tr>\n";
   $table .= "<th class=\"sitenoticeIDHeader\">ID</th>\n";
   $table .= "<th class=\"sitenoticeTextHeader\">Text</th>\n";
   $table .= "<th class=\"sitenoticeLinkHeader\">&nbsp;</th>\n";
   $table .= "<th class=\"sitenoticeLinkHeader\">&nbsp;</th>\n";
   $table .= "</tr>";
   
   while (($rowData = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
      $foundone = true;

      $table .= "<tr>\n";
      $table .= "<td style=\"text-align:center;\">" . $rowData['messageID'] . "</td>\n";
      $table .= "<td>\"" . $rowData['summary'] . ($rowData['length'] > 64 ? " ..." : "") . "\"</td>\n";
      $table .= "<td style=\"text-align:center;\"><a href=\"" . getRootURL() . "sitenotice.php?id=" . 
         $rowData['messageID'] . "\">Edit</a></td>\n";
      $table .= "<td style=\"text-align:center;\"><a href=\"" . getRootURL() . "sitenotice.php?delete=" . 
         $rowData['messageID'] . "\">Delete</a></td>\n";
      $table .= "</tr>\n";
   }

   $query->closeCursor();

   if(!$foundone){
      return "<b>There are currently no sitenotice messages.</b>";
   }
   
   return $table . "</table>\n";
}
?>
