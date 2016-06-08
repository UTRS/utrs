<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('src/languageCookie.php');
echo checkCookie();
require_once('src/exceptions.php');
require_once('src/unblocklib.php');
require_once('src/userObject.php');
require_once('src/templateObj.php');
require_once('src/appealObject.php');
require_once('src/logObject.php');
require_once('src/emailTemplates.class.php');
require_once('template.php');
require_once('src/messages.php');

$errors = '';

if(!isset($_GET['id'])){
	// don't really know how to handle this, so...
	header("Location: " . getRootURL() . "home.php");
	// ...off you go
}

$id = $_GET['id'];

if (isset($_GET['id']) && !is_numeric($id)) {
	throw new UTRSIllegalModificationException("ID must be numeric.");
}


if (isset($_GET['tid']) && !is_numeric($_GET['tid'])) {
	throw new UTRSIllegalModificationException("TID must be numeric.");
}

verifyLogin("appeal.php?id=" . $id);

skinHeader();

$appeal = Appeal::getAppealByID($id);
$admin = getCurrentUser();
$log = Log::getCommentsByAppealId($appeal->getID());

//If there is no admin with it reserved, reserve it for the current user
if ($appeal->getHandlingAdmin() == null) {
		$appeal->setHandlingAdmin($admin->getUserId());
		$appeal->update();
		$log->addNewItem('Reserved appeal', 1);
}

// confirm you have permission to email
if ($appeal->getHandlingAdmin() == null || $admin->getUserId() != $appeal->getHandlingAdmin()->getUserId()) {
	displayError("<b>Access denied:</b> You must hold the reservation on appeal number " . $id . " to send an email to that user.");
} else{
	$success = false;

	if(isset($_POST['submit'])){
		try{
			if(!isset($_POST['emailText']) | strlen($_POST['emailText']) == 0){
				throw new UTRSIllegalModificationException("You cannot send a blank email.");
			}
				
			$email = $appeal->getEmail();
			$headers = "From: Unblock Review Team <noreply-unblock@toolserver.org>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$body = "This is a reply to your Wikipedia unblock appeal from {{adminname}}, a Wikipedia administrator. " .
			        "<b>DO NOT reply to this email</b> - it is coming from an unattended email address.";
      if ($_GET['close'] == 0) {
      $body .=" If you wish "  .
					"to send a response, which may be necessary to further your appeal, please click the link below.\n".
					"<a href=\"" . getRootURL() . "reply.php?id=" . $id . "&confirmEmail=" . $email . "\">" .
					"Send a response by clicking here</a>";
      }
			$body .= "\n<hr />\n".$_POST['emailText'];
			$subject = "Response to unblock appeal #".$appeal->getID();
				
			$et = new EmailTemplates($admin, $appeal);
			$body = $et->apply_to($body);

			mail($email, $subject, $body, $headers);
			
				
			if ($_POST['template'] == "") {
				$log->addNewItem("Sent email to user", 1);
				Log::ircNotification("\x033Email sent to user\x032 " . $appeal->getCommonName() . "\x033 by \x032" . $admin->getUsername(), 0);
			} else {
				$log->addNewItem("Sent email to user using " . $_POST['template'] . " template", 1);
				Log::ircNotification("\x033Email sent to user\x032 " . $appeal->getCommonName() . "\x033 using template \x032" . $_POST['template'] . "\x033 by \x032" . $admin->getUsername(), 0);
			}
			
			//Put the contents of the email into the log
			$log->addNewItem($et->censor_email($et->apply_to($_POST['emailText'])));
						
			if (isset($_POST['statusUser']) || isset($_POST['statusClose'])) {
				//Set the appeal status if the template is set up to do that.
				if (isset($_POST['statusUser']) && $_POST['statusUser']) {
					$appeal->setStatus(Appeal::$STATUS_AWAITING_USER);
					$log->addNewItem("Status change to AWAITING_USER", 1);
				}
				if (isset($_POST['statusClose']) && $_POST['statusClose']) {
					$appeal->setStatus(Appeal::$STATUS_CLOSED);
					//Required to make the backlog work properly.  The timestamp of the 'email sent' log item and this one need a second seperation
					sleep(1);
					$log->addNewItem("Closed", 1);
				}
				$appeal->update();
				Log::ircNotification("\x033Status changed for\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . $appeal->getID() . "\x033 ) to \x032 " . $appeal->getStatus() . " \x033by \x032" . $appeal->getHandlingAdmin()->getUsername() . "\x033 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 0);
			}
			$success = true;
		}
		catch(Exception $e){
			$errors = $e->getMessage();
		}
	} elseif (isset($_POST['preview'])) {
		$body = $_POST['emailText'];

		$et = new EmailTemplates($admin, $appeal);
		$preview_content = $et->apply_to($body);
	}


	if($success){
		displaySuccess("Email sent successfully. <a href=\"" . getRootURL() . "appeal.php?id=" . $id . "\">Click" .
				" here to return to the appeal.</a>");	
	}
	else{
		?>
		<div>
		<SELECT onChange="if (this.selectedIndex != 0) { window.location='sendEmail.php?tid=' + this.value + '&id=<?php echo $_GET['id']; ?>'}">
		<?php
			
		$templates = Template::getTemplateList();
			
		if (!$templates) {
			echo "<option>No templates available</option>";
		} else {
				
			echo "<option value='-1'>Please select</option>";
		
			while (($data = $templates->fetch(PDO::FETCH_ASSOC)) !== false) {
				echo "<option value='" . $data['templateID'] . "'>" . $data['name'] . "</option>";
			}
			$templates->closeCursor();
		}
		
		?>
		</SELECT>
		</div>
		<?php

		if (isset($preview_content)) {
			?>
			<div class="mail-preview">
				<div class="mail-preview-inner">
					<h3>Rendered preview</h3>
					<div class="info"><?php echo $preview_content ?></div>
				</div>
			</div>
			<div class="mail-preview">
				<div class="mail-preview-inner">
					<h3>Raw HTML preview</h3>
					<div class="info"><?php echo htmlspecialchars($preview_content) ?></div>
				</div>
			</div>
			<?php
		}

		echo "<h3>Send an email to " . $appeal->getCommonName() . "</h3>\n";
		if($errors){
			displayError($errors);
		}

		$template = null;
		if(isset($_GET['tid'])){
			$template = Template::getTemplateById($_GET['tid']);
			$email_text = $template->getText();
		}
		
		if (isset($_POST['emailText'])) {
			$email_text = $_POST['emailText'];
		}

		
		if(isset($template)){
      if ($template->getStatusClose()) {
        echo "<form name=\"emailForm\" id=\"emailForm=\" method=\"POST\" action=\"sendEmail.php?id=" . $id . "&close=1&tid=".$template->getId();
      }
      else {echo "<form name=\"emailForm\" id=\"emailForm=\" method=\"POST\" action=\"sendEmail.php?id=" . $id . "&close=0&tid=".$template->getId();}
		}
    else { throw new UTRSIllegalModificationException("The template ID number is not set."); }
		echo "\">\n"; // closes <form>
		echo "<textarea name=\"emailText\" id=\"emailText\" rows=\"15\" cols=\"60\">";
		if(isset($email_text)){
			echo htmlspecialchars($email_text);
		}
		echo "</textarea>\n";
		if ($template) {
			if ($template->getStatusUser()) {
				echo "<b>NOTE: Using this template will set the appeal request status to AWAITING_USER</b><br>";
				echo "<input type=\"checkbox\" CHECKED value=\"true\" name=\"statusUser\">";
				echo "<label name=\"textLabel\" id=\"textLabel\" for=\"statusUser\"> Uncheck this option to prevent a status change.</label><br>";
			}
			if ($template->getStatusClose()) {
				echo "<b>NOTE: Using this template will set the appeal request status to CLOSED</b><br>";
				echo "<input type=\"checkbox\" CHECKED value=\"true\" name=\"statusClose\">";
				echo "<label name=\"textLabel\" id=\"textLabel\" for=\"statusClose\"> Uncheck this option to prevent a status change.</label><br>";
			}
		}
		echo "<input type=\"hidden\" name=\"template\" id=\"template\" value=\"" . $template->getName() . "\">";
		echo "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Send Email\" />";
		echo "<input type=\"submit\" name=\"preview\" id=\"preview\" value=\"Preview\" />";
		echo "<input type=\"reset\" name=\"reset\" id=\"reset\" value=\"Reset\" />\n";
		echo "</form>";
		echo "<p>The user will be given your UTRS username to say who sent the response, however " .
		     "they will not see your email address. You may use the following variables in your " .
		     "message:</p>\n";
		echo "<ul>\n<li>{{username}} - Gets replaced with " . $appeal->getCommonName() . "</li>\n";
		echo "<li>{{adminname}} - Gets replaced with " . $admin->getUsername() . "</li>\n";
		echo "<li>{{enwp|PAGE|TEXT}} - Gets replaced with a link to the page PAGE on the English Wikipedia, using the text TEXT.  (TEXT is optional, and the page URL will be used if this is omitted.</li>\n</ul>\n";
		echo "<br>";
		echo "<a href=\"appeal.php?id=" . $appeal->getID() . "\">Back to appeal</a>";
	}
}

skinFooter();

?>
