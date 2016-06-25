<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/appealObject.php');
require_once('src/userObject.php');
require_once('src/templateObj.php');
require_once('src/logObject.php');
require_once('src/emailTemplates.class.php');
require_once('template.php');

$errors = '';
$appeal = null;
$accessGranted = false;
$submitted = false;

if(!isset($_GET['id'])){
   // if not supposed to be here, send to appeals page
   header("Location: " . getRootURL() . "index.php");
}

try{
	//Get appeal by id
	if (is_numeric($_GET['id'])) {
		$id = $_GET['id'];
		$appeal = Appeal::getAppealByID($id);
	} else {
		throw new UTRSIllegalModificationException("The appeal ID provided is invalid. This incident has been logged.");
	}
	
	//Throw error if not reserved
	if ($appeal->getStatus() == Appeal::$STATUS_NEW) {
		throw new UTRSIllegalModificationException("Your appeal has not yet been claimed by an administrator. If you filed an" . 
				"appeal before May 24th, the text providing you with this link was sent in error.");
	}
	
   //Ensure email is the correct email for this appeal
   if(!isset($_GET['confirmEmail']) || strcmp($_GET['confirmEmail'], $appeal->getEmail()) !== 0){
      throw new UTRSIllegalModificationException("Please use the link provided to you in your email to access this page. " .
         "This security step assures us that we are still talking to the same person. Thank you.");
   }
   if(isset($_GET['token'])) {
      // Email address verification.
      $appeal->verifyEmail($_GET['token']);

      $log = Log::getCommentsByAppealId($appeal->getID());

      $log->addNewItem('Email address verified, status changed to NEW.', 1);

      Log::ircNotification("\x033 Appeal\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . 
         $appeal->getID() . " \x033) has passed email verification and is now NEW. URL: " .
         getRootURL() . "appeal.php?id=" . $appeal->getID(), 1);

      skinHeader();
      displaySuccess('Thank you for verifying your email address.  Your appeal has been updated and will be reviewed shortly.');
      skinFooter();

      exit();
   }
   if(strcmp($appeal->getStatus(), Appeal::$STATUS_CLOSED) === 0){
         $body = Template::getTemplateById(65)->getText();
         $et = new EmailTemplates("Ban Appeals Subcomittee", $appeal);
         $body = $et->apply_to($body);

      throw new UTRSIllegalModificationException($body);
         
   }
   
   $accessGranted = true;

   if(isset($_POST['submit'])){
      $submitted = true;
      
      $log = Log::getCommentsByAppealId($appeal->getID());
      
      $reply = $_POST['reply'];
      if(strlen($reply) === 0){
         throw new UTRSIllegalModificationException("You may not post a blank reply.");
      }
      
      //Post the reply to the log
      
      $log->addAppellantReply($reply);
      
      // IRC Notification
      
      $appeal->setStatus(Appeal::$STATUS_AWAITING_REVIEWER);
      $appeal->update();
      $admin = $appeal->getHandlingAdmin();
      
      Log::ircNotification("\x033" . ($admin ? "Attention\x032 " . $admin->getUsername() . "\x033: " : "") . 
         "A reply has been made to appeal\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . 
         $appeal->getID() . " \x033) and the status has been updated to AWAITING_REVIEWER URL: " .
         getRootURL() . "appeal.php?id=" . $appeal->getID(), 1);
      
      //Email notification to the admin handling the appeal
      
      if ($admin->replyNotify()) {
         $email = $admin->getEmail();
         $headers = "From: Unblock Review Team <noreply-unblock@toolserver.org>\r\n";
         $headers .= "MIME-Version: 1.0\r\n";
         $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
         $body = "Hello {{adminname}}, \n\n" .
               "This is a notification that a reply has been made to a Wikipedia unblock appeal".
               " from {{username}} that you have reserved. " .
                 "<b>DO NOT reply to this email</b> - it is coming from an unattended email address. If you wish "  .
               "to review the reply, please click the link below.\n".
               "<a href=\"" . getRootURL() . "appeal.php?id=" . $id . "\">" .
               "Review response by clicking here</a>\n<hr />\n";
         $subject = "Response to unblock appeal #".$appeal->getID();

         $et = new EmailTemplates($admin, $appeal);
         $body = $et->apply_to($body);

         $body = str_replace("\n", "<br/>", $body);
            
         mail($email, $subject, $body, $headers);
      }
   }
}
catch(UTRSException $e){
   $errors = $e->getMessage();
}

skinHeader();

if(!$accessGranted){
   displayError($errors);
}
else{
?>

<h3>Post a reply to your appeal</h3>

<?php if(!$submitted || $errors ){ ?>
<p>Welcome back, <?php echo $appeal->getCommonName(); ?>. You may use the form below to respond to any emails
you have received from the administrator(s) reviewing your block. Posting a response with this form will flag
your appeal for further attention from whoever is currently reviewing it, so if you believe your appeal has 
been heavily delayed, you can post a response to bring attention to it again.</p>

<?php } // closes if(!$submitted | $errors )

if($submitted && !$errors){
   displaySuccess("Your response has been successfully posted. We will be in touch with you again soon.");
}
else if($errors){
   displayError($errors);
}

if(!$submitted || $errors ){
?>
<form name="sendReply" id="sendReply" method="POST" action="reply.php?id=<?php echo $_GET['id'];?>&confirmEmail=<?php echo $_GET['confirmEmail'];?>">
<?php // Yes i'm being an ass and limiting the reply to 2048 charecters while admins can send much larger emails of 10k. ?>
<textarea rows="15" cols="60" name="reply" id="reply" maxlength="2048"><?php if(isset($_POST['reply'])){echo $_POST['reply'];}?></textarea>
echo '<p id="sizereply"></p>';
<input name="submit" id="submit" type="submit" value="Send Reply" />
</form>
<?php 
} // closes if(!isset($_POST['submit']) | $errors )

} // closes else from if(!$accessGranted)
?>
<?php
skinFooter();

?>
