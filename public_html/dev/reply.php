<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('../src/userObject.php');
require_once('../src/templateObj.php');
require_once('../src/logObject.php');
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
	$id = $_GET['id'];
	$appeal = Appeal::getAppealByID($id);
	if(strcmp($appeal->getStatus(), Appeal::$STATUS_CLOSED) === 0){
		throw new UTRSIllegalModificationException("Your appeal has been marked as closed, which means the adminstrator" .
		   " reviewing your appeal feels the matter is resolved. If you received a message that indicates you will be " .
		   "unblocked, but you still cannot edit, please try again in a few minutes. If you are still unable to edit," .
		   " you may wish to post <tt>{{unblock|<contents of your email here>}} to your " .
		   "<a href=\"http://enwp.org/Special:Mytalk\">User Talk: page</a>. If you appeal was declined, then you may " .
		   "wish to appeal again in several month's time, or appeal to the Ban Appeals Subcommittee by emailing " .
		   "arbcom-l AT lists DOT wikimedia DOT org.");
		   
	}
	if(!isset($_GET['confirmEmail']) || strcmp($_GET['confirmEmail'], $appeal->getEmail()) !== 0){
		throw new UTRSIllegalModificationException("Please use the link provided to you in your email to access this page. " .
		   "This security step assures us that we are still talking to the same person. Thank you.");
	}
	
	$accessGranted = true;

	if(isset($_POST['submit'])){
		$submitted = true;
		
		$log = Log::getCommentsByAppealId($appeal->getID());
		
		$reply = $_POST['reply'];
		if(strlen($reply) === 0){
			throw new UTRSIllegalModificationException("You may not post a blank reply.");
		}
		
		$log->addAppellantReply($reply);
		
		$appeal->setStatus(Appeal::$STATUS_AWAITING_REVIEWER);
		$appeal->update();
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
	echo "Submitted: " . $submitted . " Errors: " . $errors . " Not sub OR errors: " . (!$submitted || $errors) . "\n\n";
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
<textarea rows="15" cols="60" name="reply" id="reply"><?php if(isset($_POST['reply'])){echo $_POST['reply'];}?></textarea>
<input name="submit" id="submit" type="submit" value="Send Reply" />
</form>
<?php 
} // closes if(!isset($_POST['submit']) | $errors )

} // closes else from if(!$accessGranted)

skinFooter();

?>