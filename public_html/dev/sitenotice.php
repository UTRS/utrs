<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/userObject.php');
require_once('src/noticeObject.php');
require_once('src/statsLib.php');
require_once('template.php');

verifyLogin('tempMgmt.php');

$notice = null;
$message = null;
$formatMessage = null;
$error = null;
$success = null;

if(verifyAccess($GLOBALS['ADMIN'])){
	
	try{

		// If looking at a specific message, pull it up
		if(isset($_GET['id']) && strcmp($_GET['action'])){
			$notice = Notice::getNoticeById($_GET['id']);
			$message = $notice->getMessage();
			$formatMessage = $notice->getFormattedMessage();
		}

		// If previewing changes, figure out how it'll look
		if(isset($_POST['preview'])){
			$message = $_POST['message'];
			$formatMessage = Notice::format($message);
		}

		// If creating a new one, make it and redirect on success
		if(isset($_POST['save']) && isset($_GET['new'])){
			$notice = new Notice($_POST, false);
			
			header("Location: " . getRootURL() . "sitenotice.php?id=" . $notice->getMessageId());
		}
	
		// If updating an old one, change it and get new info on success
		if(isset($_POST['save']) && isset($_GET['id'])){
			$notice->update($_POST['message']);
			$message = $notice->getMessage();
			$formatMessage = $notice->getFormattedMessage();
			$success = "Changes successfully saved.";
		}
		
		if(isset($_GET['delete'])){
			Notice::delete($_GET['delete']);
			$success = "Sitenotice message #" . $_GET['delete'] . " successfully deleted.";
		}
		
	}
	catch(UTRSException $e){
		$error = $e->getMessage();
	}

}

skinHeader();

echo "<h2>Sitenotice Management</h2>\n";

if(!verifyAccess($GLOBALS['ADMIN'])){
	displayError("<b>Access denied:</b> Sitenotice management is only available to tool administrators.");
}
else{
	if($success){
		displaySuccess($success);
	}
	else if($error){
		displayError($error);
	}
	
	echo "\n";
	
	if(isset($_GET['id']) || isset($_GET['new'])){
		if(isset($_GET['id'])){
			?>
			<div id="messageInfo" >
				<div id="messageIdLabel">Message ID:</div>
				<div id="messageId"><?php echo $notice->getMessageId();?></div>
				<div id="authorLabel">Last author:</div>
				<div id="author">
					<a href="<?php echo getRootURL() + "userMgmt.php?id=" . $notice->getAuthor()->getUserId();?>">
						<?php echo $notice->getAuthor()->getUsername();?>
					</a>
				</div>
				<div id="timeLabel">Time of last edit:</div>
				<div id="timeId"><?php echo $notice->getLastEditTime();?></div>
			</div>
			<?php 
		} // close if(isset($_GET['id'])){
		
		if($formatMessage){
			?>
			<h3>Preview</h3>
			<p><?php echo $formatMessage; ?></p>
			<?php
		} // close if($formatMessage){
		
		?>
		<form 
			id="sitenoticeEdit" 
			name="sitenoticeEdit" 
			method="POST" 
			action="sitenotice.php?<?php echo (isset($_GET['new']) ? "new=true" : "id=" . $_GET['id']);?>">
			<textarea name="message" id="message" rows="6" cols="60"><?php echo $message; ?></textarea>
			<input type="submit" name="save" id="save" value="Save Message" style="font-weight:bold"/> <!-- 
		    --><input type="submit" name="preview" id="preview" value="Preview Message"/>
		</form>
		
		<h3>Formatting</h3>
		<p>You may add basic formatting to your message using the following syntax:
		<ul>
			<li>*bold text* &rarr; <b>bold text</b></li>
			<li>/italic text/ &rarr; <i>italic text</i></li>
			<li>_underlined text_ &rarr; <u>underlined text</u></li>
			<li>{http://enwp.org link text} &rarr; <a href="http://enwp.org">link text</a></li>
			<li>[red]red text[/red] &rarr; <span style="color:red">red text</span></li>
		</ul>
		Other acceptable colors include "orange", "yellow", "green", "blue", "purple", "grey", "gray", or any
		three- or six-digit hexadecimal color code starting with a # sign (i.e. #000, #FFFFFF, #a2b3c4, etc.).
		Links must start with the http:// or https:// prefix.</p>
		
		<p>Inappropriate messages or links will result in deactivation of your account.</p>
		<?php 
	} // close if(isset($_GET['id']) || isset($_GET['new'])){
	else{
		echo printSitenoticeMessages();	
	}
} // close else from if(!verifyAccess($GLOBALS['ADMIN'])){

echo "<br/><br/>\n";

skinFooter();
?>