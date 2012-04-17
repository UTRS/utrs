<?php
require_once('../public_html/src/exceptions.php');
require_once('../public_html/src/unblocklib.php');
require_once('../public_html/src/userObject.php');
require_once('../public_html/src/templateObj.php');
require_once('../public_html/src/appealObject.php');
require_once('../public_html/src/logObject.php');
require_once('../public_html/src/emailTemplates.class.php');
require_once('../public_html/template.php');

$DEBUG_MODE = true;

$db = connectToDB();
/* developed against the PDO branch, which won't work here. Fortunately, this will be an easy conversion
$query = $db->prepare("SELECT DISTINCT a.appealID
                       FROM appeal a
                       INNER JOIN comment c
                       ON a.lastLogId = c.commentID
                       WHERE a.status = :status
                       AND DateDiff(Now(), c.timestamp) > 4");

$result = $query->execute(Appeal::$STATUS_AWAITING_USER);
*/

$query = "SELECT DISTINCT a.appealID
          FROM appeal a
          INNER JOIN comment c
          ON a.lastLogId = c.commentID
          WHERE a.status = '".Appeal::$STATUS_AWAITING_USER."' 
          AND DateDiff(Now(), c.timestamp) > 4";

$result = mysql_query($query, $db);

if(!$result){
  $error = var_export($db->errorInfo(), true);
  debug('ERROR: ' . $error . '<br/>');
  throw new UTRSDatabaseException($error);
}

//needs $query->fetch() for pdo branch
while ($row = mysql_fetch_row($result)){
	try {
		$id = $row[0];
 		$appeal = Appeal::getAppealByID($id);
		$log = Log::getCommentsByAppealId($appeal->getID());
		$log->addNewItem("Sending reminder email to user", 1);
		$subject = "Your unblock request on the English Wikipedia";
		$body = "This is an automated reminder of your Wikipedia unblock appeal." .
	            "<b>DO NOT reply to this email</b> - it is coming from an unattended email address. Your unblock request "  .
	            "is still awaiting response. To provide the response, please click the link below.\n".
	 	        "<a href=\"" . getRootURL() . "reply.php?id=" . $id . "&confirmEmail=" . $appeal->getEmail() . "\">" .
		        "Send a response by clicking here</a>\n<hr />\n";
		$email_success = $appeal->sendEmail($body, $subject, User::getUserByUsername("SYSTEM"));
		if ($email_success) {
			$appeal->setStatus(Appeal::$STATUS_REMINDED, "SYSTEM");
			sleep(1);
			$log->addNewItem("Status change to " . Appeal::$STATUS_REMINDED, 1, "SYSTEM");
			$appeal->update();
		}
	} catch (Exception $e){
		$errors = $e->getMessage();
		echo($errors);
	}
}
/* developed against the PDO branch, which won't work here. Fortunately, this will be an easy conversion
$query = $db->prepare("SELECT DISTINCT a.appealID
                       FROM appeal a
                       INNER JOIN comment c
                       ON a.lastLogId = c.commentID
                       WHERE a.status = :status
                       AND DateDiff(Now(), c.timestamp) > 4");

$result = $query->execute(Appeal::$STATUS_REMINDED);
*/

$query = "SELECT DISTINCT a.appealID
          FROM appeal a
          INNER JOIN comment c
          ON a.lastLogId = c.commentID
          WHERE a.status = '".Appeal::$STATUS_REMINDED."'
          AND DateDiff(Now(), c.timestamp) > 4";

$result = mysql_query($query, $db);

if(!$result){
  $error = var_export($db->errorInfo(), true);
  debug('ERROR: ' . $error . '<br/>');
  throw new UTRSDatabaseException($error);
}

//needs $query->fetch() for pdo branch
while ($row = mysql_fetch_row($result)){
	try {
		$id = $row[0];
 		$appeal = Appeal::getAppealByID($id);
		$log = Log::getCommentsByAppealId($appeal->getID());
		$log->addNewItem("Sending close email to user", 1, "SYSTEM");
		$subject = "Your unblock request on the English Wikipedia has been closed";
		$body = "This is an automated message of your Wikipedia unblock appeal." .
	            "<b>DO NOT reply to this email</b> - it is coming from an unattended email address. Your unblock request "  .
	            "has been automatically closed, since we haven't received any feedback. If you would still like to give a response " .
		        "please open a new request <a href=\"" . getRootURL() . "\"> by clicking here</a>\n<hr />\n";
		$email_success = $appeal->sendEmail($body, $subject, User::getUserByUsername("SYSTEM"));
		if ($email_success) {
			$appeal->setStatus(Appeal::$STATUS_CLOSED, "SYSTEM");
			sleep(1);
			$log->addNewItem("Status change to " . Appeal::$STATUS_CLOSED, 1, "SYSTEM");
			$appeal->update();
		}
	} catch (Exception $e){
		$errors = $e->getMessage();
		debug($errors);
	}
}