<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('src/exceptions.php');
require_once('src/unblocklib.php');
require_once('src/userObject.php');
require_once('src/templateObj.php');
require_once('src/appealObject.php');
require_once('src/logObject.php');
require_once('template.php');

$errors = '';

if(!isset($_GET['id'])){
	// don't really know how to handle this, so...
	header("Location: " . getRootURL() . "home.php");
	// ...off you go
}

$id = $_GET['id'];

verifyLogin("appeal.php?id=" . $id);

skinHeader();

$appeal = Appeal::getAppealByID($id);
$admin = getCurrentUser();

// confirm you have permission to email
if ($appeal->getHandlingAdmin() == null ||
$admin->getUserId() != $appeal->getHandlingAdmin()->getUserId()) {
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
			        "<b>DO NOT reply to this email</b> - it is coming from an unattended email address. If you wish "  .
					"to send a response, which may be necessary to further your appeal, please click the link below.\n".
					"<a href=\"" . getRootURL() . "reply.php?id=" . $id . "&confirmEmail=" . $email . "\">" .
					"Send a response by clicking here</a>\n<hr />\n";
			$body .= $_POST['emailText'];
			$subject = "Response to your unblock appeal";
				
			$body = str_replace("{{adminname}}", $admin->getUsername(), $body);
			$body = str_replace("{{username}}", $appeal->getCommonName(), $body);
			$body = str_replace("\n", "<br/>", $body);
				
			mail($email, $subject, $body, $headers);
			
				
			$log = Log::getCommentsByAppealId($appeal->getID());
			if ($_POST['template'] == "") {
				$log->addNewItem("Sent email to user", 1);
				Log::ircNotification("\x033,0Email sent to user \x032,0" . $appeal->getCommonName() . "\x033,0 by \x032,0" . $admin->getUsername(), 1);
			} else {
				$log->addNewItem("Sent email to user using " . $_POST['template'] . " template", 1);
				Log::ircNotification("\x033,0Email sent to user \x032,0" . $appeal->getCommonName() . "\x033,0 using template \x032,0" . $_POST['template'] . "\x033,0 by \x032,0" . $admin->getUsername(), 1);
			}
						
			if (isset($_POST['statusUser']) || isset($_POST['statusClose'])) {
				//Set the appeal status if the template is set up to do that.
				if (isset($_POST['statusUser']) && $_POST['statusUser']) {
					$appeal->setStatus(Appeal::$STATUS_AWAITING_USER);
					$log->addNewItem("Status change to AWAITING_USER", 1);
				}
				if (isset($_POST['statusClose']) && $_POST['statusClose']) {
					$appeal->setStatus(Appeal::$STATUS_CLOSED);
					$log->addNewItem("Closed", 1);
				}
				$appeal->update();
				Log::ircNotification("\x033,0Status changed for \x032,0" . $appeal->getCommonName() . "\x033,0 (\x032,0 " . $appeal->getID() . "\x033,0 ) to \x032,0 " . $appeal->getStatus() . " \x033,0by \x032,0" . $appeal->getHandlingAdmin()->getUsername() . "\x033,0 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID());
			}
			$success = true;
		}
		catch(Exception $e){
			$errors = $e->getMessage();
		}
	}


	if($success){
		displaySuccess("Email sent successfully. <a href=\"" . getRootURL() . "appeal.php?id=" . $id . "\">Click" .
				" here to return to the appeal.</a>");	
	}
	else{
		?>
		<SELECT onChange="if (this.selectedIndex != 0) { window.location='sendEmail.php?tid=' + this.value + '&id=<?php echo $_GET['id']; ?>'}">
		<?php
			
		$templates = Template::getTemplateList();
			
		if (!$templates) {
			echo "<option>No templates available</option>";
		} else {
				
			echo "<option value='-1'>Please select</option>";
		
			$rows = mysql_num_rows($templates);
		
			for ($i = 0; $i < $rows; $i++) {
				$data = mysql_fetch_array($templates);
				echo "<option value='" . $data['templateID'] . "'>" . $data['name'] . "</option>";
			}
		}
		
		?>
		</SELECT>
		<?php
		echo "<h3>Send an email to " . $appeal->getCommonName() . "</h3>\n";
		if($errors){
			displayError($errors);
		}

		
		$template = null;
		if(isset($_GET['tid'])){
			$template = Template::getTemplateById($_GET['tid']);
		}

		echo "<form name=\"emailForm\" id=\"emailForm=\" method=\"POST\" action=\"sendEmail.php?id=" . $id;
		if($template){
			echo "&tid=" . $template->getId();
		}
		echo "\">\n"; // closes <form>
		echo "<textarea name=\"emailText\" id=\"emailText\" rows=\"15\" cols=\"60\">";
		if($template){
			echo $template->getText();
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
		echo "<input type=\"reset\" name=\"reset\" id=\"reset\" value=\"Reset\" />\n";
		echo "</form>";
		echo "<p>The user will be given your UTRS username to say who sent the response, however " .
		     "they will not see your email address. You may use the following variables in your " .
		     "message:</p>\n";
		echo "<ul>\n<li>{{username}} - Gets replaced with " . $appeal->getCommonName() . "</li>\n";
		echo "<li>{{adminname}} - Gets replaced with " . $admin->getUsername() . "</li>\n</ul>\n";
		echo "<br>";
		echo "<a href=\"appeal.php?id=<?php echo $_GET['id']; ?>\">Back to appeal</a>";
	}
}

skinFooter();

?>