<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/userObject.php');
require_once('src/noticeObject.php');
require_once('src/statsLib.php');
require_once('src/logObject.php');
require_once('template.php');    
require_once('sitemaintain.php');

checkOnline();

verifyLogin('tempMgmt.php');

$notice = null;
$message = null;
$formatMessage = null;
$error = null;
$success = null;

if(verifyAccess($GLOBALS['ADMIN'])){

	try{

		// If looking at a specific message, pull it up
		if(isset($_GET['id'])){
			$notice = Notice::getNoticeById($_GET['id']);
			$message = $notice->getMessage();
			$formatMessage = $notice->getFormattedMessage();
		}

		// If previewing changes, figure out how it'll look
		if(isset($_POST['preview'])){
			$message = $_POST['message'];
			if(strlen($message) > 2048){
				$formatMessage = null;
				throw new UTRSIllegalModificationException("Your message is too long. Please shorten your message to" .
					" less than 2048 characters. (Current length: " . strlen($message) . ")");
			}
			$formatMessage = Notice::format($message);
		}

		// If creating a new one, make it and redirect on success
		if(isset($_POST['save']) && isset($_GET['new'])){
			$message = $_POST['message'];
			$formatMessage = null;
			$notice = new Notice($_POST, false);

			Log::ircNotification("\x033Sitenotice\x032 " . $notice->getMessageId() .
				" \x033has been added by\x032 " . getCurrentUser()->getUsername());

			header("Location: " . getRootURL() . "sitenotice.php?id=" . $notice->getMessageId());
		}

		// If updating an old one, change it and get new info on success
		if(isset($_POST['save']) && isset($_GET['id'])){
			// save in case of error
			$message = $_POST['message'];
			$formatMessage = null;
			// update
			$notice->update($_POST['message']);
			// retrieve from updated notice
			$message = $notice->getMessage();
			$formatMessage = $notice->getFormattedMessage();
			$success = "Changes successfully saved.";

			Log::ircNotification("\x033Sitenotice\x032 " . $notice->getMessageId() .
				" \x033has been modified by\x032 " . getCurrentUser()->getUsername());
		}

		if(isset($_GET['delete'])){
			Notice::delete($_GET['delete']);
			$success = "Sitenotice message #" . $_GET['delete'] . " successfully deleted.";

			Log::ircNotification("\x033Sitenotice\x032 " . $_GET['delete'] .
				" \x033has been deleted by\x032 " . getCurrentUser()->getUsername());
		}

	}
	catch(UTRSException $e){
		$error = $e->getMessage();
	}

}

skinHeader('', true);

echo "<h2>Hook Management</h2>\n";

if(!verifyAccess($GLOBALS['ADMIN'])){
	displayError("<b>Access denied:</b> Hook management is only available to tool administrators.");
}
else{
	if($success){
		displaySuccess($success);
	}
	else if($error){
		displayError($error);
	}

	echo "\n";

	$db = connectToDB();

	$query = $db->prepare("SELECT data FROM config WHERE `config` = 'installed_hooks';");

	$result = $query->execute();

	if(!$result){
		$error = var_export($query->errorInfo(), true);
		throw new UTRSDatabaseException($error);
	}

	$values = $query->fetch(PDO::FETCH_ASSOC);
	$query->closeCursor();

	$hookArray = unserialize($values['data']);

	//Going to interject here and perform any installations

	if (isset($_GET['install'])) {
		array_push($hookArray, $_GET['install']);

		sort($hookArray);

		$query = $db->prepare("UPDATE config SET data = :hook_name WHERE `config` = 'installed_hooks';");

		$result = $query->execute(Array(":hook_name" => serialize($hookArray)));

		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}
	}

	//Also going to uninstall

	if (isset($_GET['uninstall'])) {
		unset($hookArray[array_search($_GET['uninstall'], $hookArray)]);

		$hookArray = array_values($hookArray);

		sort($hookArray);

		$query = $db->prepare("UPDATE config SET data = :hook_name WHERE `config` = 'installed_hooks';");

		$result = $query->execute(Array(":hook_name" => serialize($hookArray)));

		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}
	}

	$hook_count = count($hookArray);

	echo "<h3>Installed Hooks</h3>";
	for ($i = 0; $i < $hook_count; $i++) {
			echo $hookArray[$i] . " - <a href=\"?uninstall=" . $hookArray[$i] . "\">Uninstall</a><br>";
	}

	echo "<br><h3>Uninstalled Hooks</h3>";

	//path to directory to scan
	$directory = "hooks/";

	//get all image files with a .jpg extension.
	$hooks = glob($directory . "*.php");

	//print each file name
	foreach($hooks as $hook)
	{
		$hook_name = substr(substr($hook, 6), 0, -4);
		if (!in_array($hook_name, $hookArray))
			echo $hook_name . " - <a href=\"?install=" . $hook_name . "\">Install</a><br>";
	}
} // close else from if(!verifyAccess($GLOBALS['ADMIN'])){

echo "<br/><br/>\n";

skinFooter();
?>