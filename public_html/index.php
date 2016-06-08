<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('src/languageCookie.php');
echo checkCookie();
$lang=getCookie();
require_once('recaptchalib.php');
require_once('template.php');
require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/appealObject.php');
require_once('src/banObject.php');
require_once('src/logObject.php');
require_once('src/messages.php');

$publickey = @$CONFIG['recaptcha']['publickey'];
$privatekey = @$CONFIG['recaptcha']['privatekey'];
$captchaErr = null;
$errorMessages = '';
$appeal = null;
$email = null;
$blocker = null;
$appealText = null;
$edits = null;
$blockReason = null;
$otherInfo = null;
$hasAccount = null;
$wikiAccount = null;
$autoBlock = null;

if(loggedIn()){
   header("Location: " . getRootURL() . "home.php");
}

$success = false;

// Handle submitted form
if(isset($_POST["submit"])){
   
   debug('form submitted <br/>');
   
   try{
      if (isset($privatekey)) {
         // verify captcha
         $resp = recaptcha_check_answer($privatekey,
               $_SERVER["REMOTE_ADDR"],
               $_POST["recaptcha_challenge_field"],
               $_POST["recaptcha_response_field"]);
            
         if(!$resp->is_valid) {
            $captchaErr = $resp->error;
            throw new UTRSValidationException(SystemMessages::$error['BadCaptcha'][$lang]);
         }

         debug('captcha valid <br/>');
      }
      
      $ip = Appeal::getIPFromServer();
      $email = $_POST["appeal_email"];
      $registered = (isset($_POST["appeal_hasAccount"]) ? ($_POST["appeal_hasAccount"] ? true : false) : false);
      $wikiAccount = (isset($_POST["appeal_wikiAccountName"]) ? $_POST["appeal_wikiAccountName"] : null);
      if (isset($_POST["appeal_autoblock"]) && $_POST["appeal_autoblock"] == 1) {
        $autoblock = true;      
      }
      if (!isset($_POST["appeal_autoblock"]) || (isset($_POST["appeal_autoblock"]) && $_POST["appeal_autoblock"] == 0)) {
        $autoblock = false;      
      }
       
      $ban = Ban::isBanned($ip, $email, $wikiAccount);
      if($ban){
         $expiry = $ban->getExpiry();
         $avoidable = strcmp($ban->getTarget(), $wikiAccount) == 0 && !$registered;
         $message = ($ban->isIP() ? SystemMessages::$system['YourIP'][$lang] : $ban->getTarget()) . " ".SystemMessages::$system['HasBanned'][$lang]. " ".
            ($expiry ? SystemMessages::$system['Until'][$lang]." " . $expiry : SystemMessages::$system['Indef'][$lang]) . " " .SystemMessages::$system['by'][$lang]. " " . $ban->getAdmin()->getUsername() .
            " ".SystemMessages::$system['Reason'][$lang]." '" . $ban->getReason() . "'.";
         if($avoidable){
            $message .= " ".SystemMessages::$system['DiffUName'][$lang];
         }
         else{
            $message .= " ".SystemMessages::$system['StillAppeal'][$lang];
         }
         throw new UTRSCredentialsException($message);
      }
      if ($registered && !$autoblock && !Appeal::verifyBlock($wikiAccount, TRUE)) {
        throw new UTRSValidationException(SystemMessages::$system['Uname'][$lang] ."(".$wikiAccount.")". SystemMessages::$tos['NotBlocked'][$lang]." ".SystemMessages::$system['VerifyBlock'][$lang]);
      }
      elseif ($registered && $autoblock  && !Appeal::verifyBlock($ip, FALSE)) {
        throw new UTRSValidationException(SystemMessages::$system['YourIP'][$lang]. "(".$ip.")". SystemMessages::$tos['NotBlocked'][$lang]." ".SystemMessages::$system['IsAccountBlocked'][$lang]);
      }
      elseif (!$registered && !Appeal::verifyBlock($ip, FALSE)) {
        throw new UTRSValidationException(SystemMessages::$system['YourIP'][$lang]. "(".$ip.")". SystemMessages::$tos['NotBlocked'][$lang]." ". SystemMessages::$system['HaveAccount'][$lang]);
      }
      if ($registered && !Appeal::verifyNoPublicAppeal($wikiAccount)) {
        throw new UTRSValidationException(SystemMessages::$tos['AppealTalkpage'][$lang]);
      }
      elseif (!$registered && !Appeal::verifyNoPublicAppeal($ip)) {
        throw new UTRSValidationException(SystemMessages::$tos['AppealTalkpage'][$lang]);
      }
      if ($registered) {
	      if (Appeal::activeAppeal($email, $wikiAccount)) {
	        throw new UTRSValidationException(SystemMessages::$tos['AlreadySubmitted'][$lang]);
	      }
      }
	  else {
		  if (Appeal::activeAppeal($email, NULL)) {
		  	throw new UTRSValidationException(SystemMessages::$tos['AlreadySubmitted'][$lang]);
		  }
      }

      $appeal = Appeal::newUntrusted($_POST);
      debug('object created <br/>');

      $appeal->insert();

      // Send confirmation email.
      $confirmURL = getRootURL() . "reply.php?id=". $appeal->getID() .
         "&confirmEmail=" . urlencode($appeal->getEmail()) .
         "&token=" . $appeal->getEmailToken();

      $headers = "From: Unblock Review Team <noreply-unblock@toolserver.org>\r\n";
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
      $body = "This is an automated message from the English Wikipedia Unblock Ticket Request System. " .
         "In order for your appeal to be processed, you need to confirm that the email address " .
         "you entered with your appeal is valid. To do this, simply click the link below.  If you " .
         "did not file an appeal then simply do nothing, and the appeal will be deleted.<br /><br />" .
         "<a href=\"" . htmlspecialchars($confirmURL) . "\">" . htmlspecialchars($confirmURL) . "</a>";

      mail($appeal->getEmail(), "Unblock appeal email address confirmation", $body, $headers);

      $success = true;
      
      $system = Log::getCommentsByAppealId($appeal->getID());
      $system->addNewItem(SystemMessages::$error['AppealCreated'][$lang], 1);
      Log::ircNotification("\x033New appeal has been created for\x032 " . $appeal->getCommonName() . " \x033(\x032 " . $appeal->getID() . " \x033) URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 1);
   }
   catch (UTRSValidationException $ex){
   	  $errorMessages = $ex->getMessage() . $errorMessages;
   	  $hasAccount = (isset($_POST["appeal_hasAccount"]) ? ($_POST["appeal_hasAccount"] ? true : false) : false);
   	  $autoBlock = (isset($_POST["appeal_autoblock"]) ? ($_POST["appeal_autoblock"] ? true : false) : false);
   }
   catch(UTRSException $ex){
      $errorMessages = $ex->getMessage() . $errorMessages;
      $hasAccount = (isset($_POST["appeal_hasAccount"]) ? ($_POST["appeal_hasAccount"] ? true : false) : false);
      $autoBlock = (isset($_POST["appeal_autoblock"]) ? ($_POST["appeal_autoblock"] ? true : false) : false);
      // TODO: not sure how to include the other fields due to the javascript
   }
   catch(ErrorException $ex){
      $errorMessages = $ex->getMessage() . $errorMessages;
      $hasAccount = (isset($_POST["appeal_hasAccount"]) ? ($_POST["appeal_hasAccount"] ? true : false) : false);
      $autoBlock = (isset($_POST["appeal_autoblock"]) ? ($_POST["appeal_autoblock"] ? true : false) : false);
   }
}

skinHeader("var accountNameInput = \"<label id=\\\"accountNameLabel\\\" for=\\\"accountName\\\" class=\\\"required\\\">". SystemMessages::$system['NameOfAccount'][$lang]."</label> <input id=\\\"accountName\\\" type=\\\"text\\\" name=\\\"appeal_wikiAccountName\\\" value=\\\"" . posted('appeal_wikiAccountName') . "\\\"/><br />\";
var autoBlockInput = \"<label id=\\\"autoBlockLabel\\\" for=\\\"autoBlock\\\" class=\\\"required\\\">".SystemMessages::$system['WhatIsBlocked'][$lang]."</label> &#09; <input id=\\\"autoBlockN\\\" type=\\\"radio\\\" name=\\\"appeal_autoblock\\\" value=\\\"0\\\" " . ($hasAccount ? ($autoBlock ? "" : "checked=\\\"checked\\\"") : "") . " />". SystemMessages::$system['MyAccount'][$lang]." &#09; <input id=\\\"autoBlockY\\\" type=\\\"radio\\\" name=\\\"appeal_autoblock\\\" value=\\\"1\\\" " . ($hasAccount ? ($autoBlock ? "checked=\\\"checked\\\"" : "") : "") . " />". SystemMessages::$system['MyIPorRange'][$lang]."<br />\";
var desiredAccountInput = \"<label id=\\\"accountNameLabel\\\" for=\\\"accountName\\\">".SystemMessages::$log['CreateAccount'][$lang]."</label><br/><input id=\\\"accountName\\\" type=\\\"text\\\" name=\\\"appeal_wikiAccountName\\\" value=\\\"" . posted('appeal_wikiAccountName') . "\\\"/><br />\";
var registered = " . ($hasAccount ? "true" : "false") . ";

function hasAccount(){
   var span = document.getElementById(\"variableQuestionSection\");
   span.innerHTML = accountNameInput + \"\\n\" + autoBlockInput;
}

function noAccount() {
   var span = document.getElementById(\"variableQuestionSection\");
   span.innerHTML = desiredAccountInput;
} " . (isset($_POST['appeal_hasAccount']) ? "

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
<center><b><?php echo SystemMessages::$system['Welcome'][$lang] ?></b>
<div id="inputBox">
<?php
if($success){
   displaySuccess(SystemMessages::$system['AppealSucess'][$lang]);
} else {
?>
<p>If you are presently blocked from editing on Wikipedia (which you may verify by 
clicking <a href="http://en.wikipedia.org/w/index.php?title=Wikipedia:Sandbox&action=edit">here</a>), you may fill out
the form below to have an administrator review your block. Please complete all fields labelled in 
<span class="required">red text</span>, as these are required in order for us to complete a full review of your block.</p>

<p>If you are having trouble editing a particular page or making a particular edit, but are able to edit the page
linked in the previous paragraph, you may not be blocked, but instead could be having difficulty with 
<a href="http://en.wikipedia.org/wiki/Wikipedia:Protection policy">page protection</a> or the 
<a href="http://en.wikipedia.org/wiki/Wikipedia:Edit filter">edit filter</a>. For more information, and instructions on
how to receive assistance, please see those links.</p>

<p><b><?php echo SystemMessages::$system['AssistBlock'][$lang]?></b></p>

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

echo '<form name="unblockAppeal" id="unblockAppeal" action="index.php" method="POST">';
echo '<label id="emailLabel" for="accountName" class="required">'.SystemMessages::$system['ReqEmail'][$lang].'</font></b></label> <input id="email" type="text" name="appeal_email" value="' . posted('appeal_email') . '"/><br /><br />';
echo '<label id="registeredLabel" for="registered" class="required">'.SystemMessages::$system['HaveAccount'][$lang].'</label> &#09; <input id="registeredY" type="radio" name="appeal_hasAccount" value="1" onClick="hasAccount()" ' . (isset($_POST['appeal_hasAccount']) ? ($hasAccount ? 'checked="checked"' : '') : "") . ' /> Yes &#09; <input id="registeredN" type="radio" name="appeal_hasAccount" value="0" onClick="noAccount()" ' . (isset($_POST['appeal_hasAccount']) ? (!$hasAccount ? 'checked="checked"' : '') : '') . ' /> No<br /><br />';
echo '<span id="variableQuestionSection"></span><br />';
echo '<!--<label id="blockingAdminLabel" for="blockingAdmin">According to your block message, which administrator placed this block?</label>  --><input id="blockingAdmin" type="hidden" name="appeal_blockingAdmin" value="No one"/><!--<br /><br />-->';
echo '<label id="appealLabel" for="appeal" class="required">'.SystemMessages::$system['WhyUnblock'][$lang].'</label><br /><br />';
echo '<textarea id="appeal" name="appeal_appealText" rows="5" >' . posted('appeal_appealText') . '</textarea><br /><br />';
echo '<label id="editsLabel" for="edits" class="required">'.SystemMessages::$system['WhatEdit'][$lang].'</label><br /><br />';
echo '<textarea id="edits" name="appeal_intendedEdits" rows="5" >' . posted('appeal_intendedEdits') . '</textarea><br /><br />';
echo '<label id="blockInfoLabel" for="blockReaon" class="required">Why do you think there is a block currently affecting you? If you believe it\'s in error, tell us how.</label><br /><br />';
echo '<textarea id="block" name="appeal_blockReason" rows="5" >' . posted('appeal_blockReason') . '</textarea><br /><br />';
echo '<label id="otherInfoLabel" for="otherInfo">'.SystemMessages::$system['AnythingElse'][$lang].'</label><br /><br />';
echo '<textarea id="otherInfo" name="appeal_otherInfo" rows="3" >' . posted('appeal_otherInfo') . '</textarea><br /><br />';

if (isset($privatekey)) {
   echo '<span class="overridePre">';
   if($captchaErr == null){
      echo recaptcha_get_html($publickey, null, true);
   }
   else{
      echo recaptcha_get_html($publickey, $captchaErr, true);
   }
   echo '</span>';
}

?>
<small>
<p>By submitting this unblock request, you are consenting to allow us to collect information about
your computer and that you agree with our <a href="privacy.php">Privacy Policy</a>.  This information
will in most cases allow us to distinguish you from any vandals editing from the same location. We do
not store this information any longer than necessary, and do not share it with any third party. For more
information, please see our <a href="privacy.php">Privacy Policy.</a>

<?php echo SystemMessages::$privpol_all['WikimediaLabsDisclaimer'][$lang]?></p></small>
<?php

echo '<input type="submit" name="submit" value='.SystemMessages::$system['SubmitAppeal'][$lang].'>';
echo '</form>';

} /* !$success */
?>

<p><?php echo SystemMessages::$information['AppealSubmitInfo'][$lang]?></p>
</div></center>
<?php 

skinFooter();
?>
