<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('recaptchalib.php');
require_once('template.php');
require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/appealObject.php');
require_once('src/banObject.php');
require_once('src/logObject.php');

$publickey = '6Le92MkSAAAAANADTBB8wdC433EHXGpuP_v1OaOO';
$privatekey = '6Le92MkSAAAAAH1tkp8sTZj_lxjNyBX7jARdUlZd';
$captchaErr = null;
$errorMessages = '';
$appeal = null;
$email = null;
$blocker = null;
$appealText = null;
$edits = null;
$otherInfo = null;
$hasAccount = null;
$wikiAccount = null;
$autoBlock = null;

if(loggedIn()){
	header("Location: " . getRootURL() . "home.php");
}

// Handle submitted form
if(isset($_POST["submit"])){
	
	debug('form submitted <br/>');
	
	try{
		// verify captcha
		$resp = recaptcha_check_answer($privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]);
			
		if(!$resp->is_valid) {
			$captchaErr = $resp->error;
			throw new UTRSValidationException('<br />The response you provided to the captcha was not correct. Please try again.');
		}
		
		debug('captcha valid <br/>');
		
		$ip = Appeal::getIPFromServer();
		$email = $_POST["email"];
		$registered = (isset($_POST["registered"]) ? ($_POST["registered"] ? true : false) : false);
		$wikiAccount = (isset($_POST["accountName"]) ? $_POST["accountName"] : null);
		 
		$ban = Ban::isBanned($ip, $email, $wikiAccount);
		if($ban){
			$expiry = $ban->getExpiry();
			$avoidable = strcmp($ban->getTarget(), $wikiAccount) == 0 && !$registered;
			$message = ($ban->isIP() ? "Your IP address" : $ban->getTarget()) . " has been banned " . 
				($expiry ? "until " . $expiry : "indefinitely") . " by " . $ban->getAdmin()->getUsername() .
				" for the reason '" . $ban->getReason() . "'.";
			if($avoidable){
				$message .= " You may be able to resubmit your appeal by selecting a different username.";
			}
			else{
				$message .= " If you still wish to appeal your block, you may visit us on IRC at " . 
					"<a href=\"http://webchat.freenode.net/?channels=wikipedia-en-unblock\">#wikipedia-en-unblock</a> " .
				    "(if you haven't already done so) or email the Ban Appeals Subcommittee at " .
					"<tt>arbcom-appeals-en@lists.wikimedia.org</tt> .";
			}
			throw new UTRSCredentialsException($message);
		}

		Appeal::validate($_POST);
		
		debug('validation done <br/>');
	
		$appeal = new Appeal($_POST, false);
		debug('object created <br/>');
		
		$log = Log::getCommentsByAppealId($appeal->getID());
		$log->addNewItem("Appeal Created", 1);
		Log::ircNotification("\x033,0New appeal has been created for\x032,0 " . $appeal->getCommonName() . " \x033,0(\x032,0 " . $appeal->getID() . " \x033,0) URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 1);
	}
	catch(UTRSException $ex){
		$errorMessages = $ex->getMessage() . $errorMessages;
		$email = $_POST["email"];
		$blocker = $_POST["blockingAdmin"];
		$appealText = sanitizeText($_POST["appeal"]);
		$edits = sanitizeText($_POST["edits"]);
		$otherInfo = sanitizeText($_POST["otherInfo"]);
		$hasAccount = (isset($_POST["registered"]) ? ($_POST["registered"] ? true : false) : false);
		$wikiAccount = $_POST["accountName"];
		$autoBlock = (isset($_POST["autoBlock"]) ? ($_POST["autoBlock"] ? true : false) : false);
		// TODO: not sure how to include the other fields due to the javascript
	}
	catch(ErrorException $ex){
		$errorMessages = $ex->getMessage() . $errorMessages;
		$email = $_POST["email"];
		$blocker = $_POST["blockingAdmin"];
		$appealText = $_POST["appeal"];
		$edits = $_POST["edits"];
		$otherInfo = $_POST["otherInfo"];
		$hasAccount = (isset($_POST["registered"]) ? ($_POST["registered"] ? true : false) : false);
		$wikiAccount = $_POST["accountName"];
		$autoBlock = (isset($_POST["autoBlock"]) ? ($_POST["autoBlock"] ? true : false) : false);
	}
}

skinHeader("var accountNameInput = \"<label id=\\\"accountNameLabel\\\" for=\\\"accountName\\\" class=\\\"required\\\">What is the name of your account?</label> <input id=\\\"accountName\\\" type=\\\"text\\\" name=\\\"accountName\\\" value=\\\"" . $wikiAccount . "\\\"/><br />\";
var autoBlockInput = \"<label id=\\\"autoBlockLabel\\\" for=\\\"autoBlock\\\" class=\\\"required\\\">What has been blocked?</label> &#09; <input id=\\\"autoBlockN\\\" type=\\\"radio\\\" name=\\\"autoBlock\\\" value=\\\"0\\\" " . ($hasAccount ? ($autoBlock ? "" : "checked=\\\"checked\\\"") : "") . " /> My account &#09; <input id=\\\"autoBlockY\\\" type=\\\"radio\\\" name=\\\"autoBlock\\\" value=\\\"1\\\" " . ($hasAccount ? ($autoBlock ? "checked=\\\"checked\\\"" : "") : "") . " /> My IP address or range (my account is not blocked)<br />\";
var desiredAccountInput = \"<label id=\\\"accountNameLabel\\\" for=\\\"accountName\\\">We may be able to create an account for you which you can use to avoid problems like this in the future. If you would like for us to make an account for you, please enter the username you'd like to use here.</label><br/><input id=\\\"accountName\\\" type=\\\"text\\\" name=\\\"accountName\\\" value=\\\"" . $wikiAccount . "\\\"/><br />\";
var registered = " . ($hasAccount ? "true" : "false") . ";

function hasAccount(){
	var span = document.getElementById(\"variableQuestionSection\");
	span.innerHTML = accountNameInput + \"\\n\" + autoBlockInput;
}

function noAccount() {
	var span = document.getElementById(\"variableQuestionSection\");
	span.innerHTML = desiredAccountInput;
} " . (isset($_POST['registered']) ? "

window.onload = function ()
{
	if(registered){
		hasAccount();
	}
	else{
		noAccount();
	}
}; " : "" ));
?>
<center><b>Welcome to the Unblock Ticket Request System.</b></center>

<p>If you are presently blocked from editing on Wikipedia (which you may verify by 
clicking <a href="http://en.wikipedia.org/w/index.php?title=Wikipedia:Sandbox&action=edit">here</a>), you may fill out
the form below to have an administrator review your block. Please complete all fields labelled in 
<span class="required">red text</span>, as these are required in order for us to complete a full review of your block.</p>

<p>If you are having trouble editing a particular page or making a particular edit, but are able to edit the page
linked in the previous paragraph, you may not be blocked, but instead could be having difficulty with 
<a href="http://en.wikipedia.org/wiki/Wikipedia:Protection policy">page protection</a> or the 
<a href="http://en.wikipedia.org/wiki/Wikipedia:Edit filter">edit filter</a>. For more information, and instructions on
how to receive assistance, please see those links.</p>

<p><b>For assistance with a block, please complete the form below:</b></p>

<noscript>
<?php displayError("It looks like your browser either doesn't support Javascript, or " .
                             "Javascript is disabled. Elements of this form require Javascript " .
                             "to display properly. Please enable Javascript or use another browser " .
                             "to continue. Thank you!");?>
</noscript>

<?php 
if($errorMessages){
	displayError($errorMessages);
}
if($appeal != null){
	displaySuccess("Thank you! Your appeal has been accepted and will be reviewed soon.");
}

echo '<form name="unblockAppeal" id="unblockAppeal" action="index.php" method="POST">';
echo '<label id="emailLabel" for="accountName" class="required">What is your email address? We will need this to respond to your appeal.</label> <input id="email" type="text" name="email" value="' . $email . '"/><br /><br />';
echo '<label id="registeredLabel" for="registered" class="required">Do you have an account on Wikipedia?</label> &#09; <input id="registeredY" type="radio" name="registered" value="1" onClick="hasAccount()" ' . (isset($_POST['registered']) ? ($hasAccount ? 'checked="checked"' : '') : "") . ' /> Yes &#09; <input id="registeredN" type="radio" name="registered" value="0" onClick="noAccount()" ' . (isset($_POST['registered']) ? (!$hasAccount ? 'checked="checked"' : '') : '') . ' /> No<br /><br />';
echo '<span id="variableQuestionSection"></span><br />';
echo '<label id="blockingAdminLabel" for="blockingAdmin" class="required">According to your block message, which administrator placed this block?</label>  <input id="blockingAdmin" type="text" name="blockingAdmin" value="' . $blocker . '"/><br /><br />';
echo '<label id="appealLabel" for="appeal" class="required">Why do you believe you should be unblocked?</label><br /><br />';
echo '<textarea id="appeal" name="appeal" rows="5" >' . $appealText . '</textarea><br /><br />';
echo '<label id="editsLabel" for="edits" class="required">If you are unblocked, what articles do you intend to edit?</label><br /><br />';
echo '<textarea id="edits" name="edits" rows="5" >' . $edits . '</textarea><br /><br />';
echo '<label id="otherInfoLabel" for="otherInfo">Is there anything else you would like us to consider when reviewing your block?</label><br /><br />';
echo '<textarea id="otherInfo" name="otherInfo" rows="3" >' . $otherInfo . '</textarea><br /><br />';

echo '<span class="overridePre">';
if($captchaErr == null){
	echo recaptcha_get_html($publickey);
}
else{
	echo recaptcha_get_html($publickey, $captchaErr);
}
echo '</span>';

echo '<p>By submitting this unblock request, you are consenting to allow us to collect information about ' .
     'your computer and that you agree with our <a href="privacy.html">Privacy Policy</a>.  This information ' .
     'will in most cases allow us to distinguish you from any vandals editing from the same location. We do ' .
     'not store this information any longer than necessary, and do not share it with any third party. For more ' .
     'information, please see our <a href="privacy.html">Privacy Policy.</a></p>';

echo '<input type="submit" name="submit" value="Submit Appeal"/>';
echo '</form>';
?>

<p>Please remember that Wikipedia administrators are volunteers; it may take some time for your appeal to be reviewed, and a courteous appeal will be met with a courteous response. If you feel it is taking too long for your appeal to be reviewed, you can usually appeal your block on your user talk page (<a href="http://en.wikipedia.org/wiki/Special:Mytalk">located here</a>) by copying this text and pasting it in a new section on the bottom of your page: <b><tt>{{unblock|1=your reason here}}</tt></b> Be sure to replace "your reason here" with your appeal.</p>

<?php 

skinFooter();
?>