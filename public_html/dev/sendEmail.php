<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');
require_once('../src/userObject.php');
require_once('../src/templateObj.php');
require_once('../src/appealObject.php');
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
echo "test1";
// confirm you have permission to email
if (!isset($appeal->getHandlingAdmin()) ||
$appeal->getHandlingAdmin() == null ||
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
			$from = "From: Unblock Review Team <noreply-unblock@toolserver.org>";
			$body = "This is a reply to your Wikipedia unblock appeal from {{adminname}}, a Wikipedia administrator. " .
			        "<b>DO NOT reply to this email</b> - it is coming from an unattended email address. If you wish "  .
					"to send a response, which may be necessary to further your appeal, please click the link below.\n".
					"<a href=\"" . getRootURL() . "reply.php?id=" . $id . "&confirmEmail=" . $email . "\">" .
					"Send a response by clicking here</a>\n<hr />\n";
			$body .= $_POST['emailText'];
			$subject = "Response to your unblock appeal";
				
			$body = str_replace("{{adminname}}", $admin->getUsername(), $body);
			$body = str_replace("{{username}}", $appeal->getCommonName(), $body);
				
			mail($email, $subject, $body, $from);
				
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
		echo "<h3>Send an email to " . $appeal->getCommonName() . "</h3>\n";
		if($errors){
			displayError($errors);
		}

		echo "test3";
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
		echo "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Send Email\" />";
		echo "<input type=\"reset\" name=\"reset\" id=\"reset\" value=\"Reset\" />\n";
		echo "</form>";
		echo "<p>The user will be given your UTRS username to say who sent the response, however " .
		     "they will not see your email address. You may use the following variables in your " .
		     "message:</p>\n";
		echo "<ul>\n<li>{{username}} - Gets replaced with " . $appeal->getCommonName() . "</li>\n";
		echo "<li>{{adminname}} - Gets replaced with " . $admin->getUsername() . "</li>\n</ul>\n";
	}
}

skinFooter();

?>