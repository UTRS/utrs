<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('recaptchalib.php');
require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/userObject.php');
require_once('src/logObject.php');
require_once('template.php');

$publickey = @$CONFIG['recaptcha']['publickey'];
$privatekey = @$CONFIG['recaptcha']['privatekey'];
$captchaErr = null;
$errorMessages = '';

$username = null;
$email = null;
$wikiAccount = null;
$useSecure = null;
$user = null;
$diff = null;

// Handle submitted form
if(isset($_POST["submit"])){
   
   debug('form submitted <br/>');
   
   try{
      
      $username = $_POST["username"];
      $email = $_POST["email"];
      $wikiAccount = $_POST["wikiAccount"];
      $useSecure = isset($_POST["useSecure"]);
      $diff = $_POST["diff"];
      
      if (isset($publickey)) {
         // verify captcha
         $resp = recaptcha_check_answer($privatekey,
               $_SERVER["REMOTE_ADDR"],
               $_POST["recaptcha_challenge_field"],
               $_POST["recaptcha_response_field"]);
            
         if(!$resp->is_valid) {
            $captchaErr = $resp->error;
            $errorMessages = 'The response you provided to the captcha was not correct. Please try again.';
         }
         
         debug('captcha valid <br/>');
      }
      
      $username = TRIM($username);
      $email = TRIM($email);
      $wikiAccount = TRIM($wikiAccount);
      
      if($username === '' || $username == null){
         if($errorMessages != null){
            $errorMessages .= '<br/>';
         }
         $errorMessages .= ($errorMessages ? '\n' : '') . 'Username is required.';
      }
      if($_POST['password'] === '' || $_POST['password'] == null){
         if($errorMessages != null){
            $errorMessages .= '<br/>';
         }
         $errorMessages .= ($errorMessages ? '\n' : '') . 'A password is required.';
      }
      else if(strlen($_POST['password']) < 4){
         if($errorMessages != null){
            $errorMessages .= '<br/>';
         }
         $errorMessages .= ($errorMessages ? '\n' : '') . 'Passwords must be at least 4 characters long.';
      }
      if($email === '' || $email == null){
         if($errorMessages != null){
            $errorMessages .= '<br/>';
         }
         $errorMessages .= ($errorMessages ? '\n' : '') . 'A valid email address is required.';
      }
      if($wikiAccount === '' || $wikiAccount == null){
         if($errorMessages != null){
            $errorMessages .= '<br/>';
         }
         $errorMessages .= ($errorMessages ? '\n' : '') . 'The name of your Wikipedia account is required.';
      }
      if(strpos($username, "#") !== false | strpos($username, "/") !== false |
         strpos($username, "|") !== false | strpos($username, "[") !== false |
         strpos($username, "]") !== false | strpos($username, "{") !== false |
         strpos($username, "}") !== false | strpos($username, "<") !== false |
         strpos($username, ">") !== false | strpos($username, "@") !== false |
         strpos($username, "%") !== false | strpos($username, ":") !== false | 
         strpos($username, '$') !== false){
            $errorMessages .= ($errorMessages ? '\n' : '') . 'The username you have entered is invalid. Usernames ' .
               'may not contain the characters # / | [ ] { } < > @ % : $';
      }
      if($diff === '' || $diff == null){
         $errorMessages .= ($errorMessages ? '\n' : '') . 'A confirmation diff link is required.';
      }
      $urlWikiAccount = urlencode(str_replace(" ", "_", $wikiAccount));
      if(strpos($diff, "diff") === false || strpos($diff, "User_talk:" . $urlWikiAccount) === false){
         $errorMessages .= ($errorMessages ? '\n' : '') . 'You must provide a valid confirmation diff to your user talk page. Please note ' .
            'that this form requires that "title=User_talk:[...]" be included in your diff link.';
      }
      
      if(!$errorMessages){
         $user = new User($_POST, false);
         debug('object created<br/>');
         Log::ircNotification("\x033New user account\x032 " . $user->getUsername() . "\x033 has been requested. URL: " . getRootURL() . "userMgmt.php?userId=" . $user->getUserId());
      }
   }
   catch(UTRSException $ex){
      $errorMessages = ($errorMessages ? '\n' : '') . $ex->getMessage() . '<br />' . $errorMessages;
   }
   catch(ErrorException $ex){
      $errorMessages = ($errorMessages ? '\n' : '') . 'An error occured: ' . $ex->getMessage() . '<br />' . $errorMessages;
   }
   
}

skinHeader('
var talkEditLink = "en.wikipedia.org/wiki/Special:Mytalk?action=edit&section=new" +
      "&preloadtitle=UTRS%20Account%20Request&preload=User:Hersfold/UTRSpreload";

function getTalkEditLink(){
   var checkbox = document.getElementById("useSecure");
   if(checkbox.checked){
      return "https://" + talkEditLink;
   }
   else{
      return "http://" + talkEditLink;
   }
}

function replaceTalkEditLink(){
   var link = document.getElementById("editLink");
   link.href = getTalkEditLink();
}
');

?>
<center><b>Welcome to the Unblock Ticket Request System.</b>
<div id="inputBox">
<p>Administrators who wish to assist in reviewing blocks through this system may register
an account here. Your account must be approved by a tool administrator before it may 
be used on this site. If you have already requested an account, but it has gone unapproved
for some time, please email the development team at 
<a href="mailto:utrs-developers@googlegroups.com?subject=Account approval">utrs-developers@googlegroups.com</a>.</p>

<p>After completing this form, please add a new section to your user talk page, with the
heading "UTRS Account Request", while logged into your administrator account in order
to confirm that you have requested an account here. A link will be provided for this 
purpose once your account has been created. Thank you.</p>

<?php 
if($errorMessages){
   displayError($errorMessages);
}
if($user != null){
   echo '<center><b>Thank you, ' . $user->getUsername() . '. Your account has been created ';
   echo 'and is awaiting approval by a tool administrator.</b></center>';
   echo '<br/>';
}
else{
   echo '<p>Fields in red are required. Passwords must be at least four characters in length.</p>';
   echo '<form name="accountRegister" id="accountRegister" action="register.php" method="POST">';
   echo '<label id="usernameLabel" for="username" class="required">Username:</label> <input id="username" type="text" name="username" value="' . $username . '"/><br/><br/>';
   echo '<label id="passwordLabel" for="password" class="required">Password:</label> <input id="password" type="password" name="password" "/><br/><br/>';
   echo '<label id="wikiAccountLabel" for="wikiAccount" class="required">Wikipedia username:</label> <input id="wikiAccount" type="text" name="wikiAccount" value="' . $wikiAccount . '"/><br/><br/>';
   echo '<label id="emailLabel" for="email" class="required">Email address:</label> <input id="email" type="text" name="email" value="' . $email . '"/><br/><br/>';
   echo '<label id="useSecureLabel" for="useSecure">Do you want links to Wikipedia to use the secure server?</label> <input id="useSecure" type="checkbox" name="useSecure" onClick="replaceTalkEditLink()" ' . ($useSecure ? 'checked="true"' : '') . '/><br/><br/>';
   
   echo '<p>To complete the registration process, please click on the link below ';
   echo 'to edit your user talk page and confirm your creation of an account. ';
   echo 'Without this verification, your account <i>will not</i> be approved. ';
   echo 'Please leave this section on your talk page until you receive notice that ';
   echo 'your account is approved; if you use an archival bot, you may wish to ';
   echo 'disable it temporarily or remove timestamps from this section. Once you have';
   echo 'completed this edit, please provide a link to the diff of your edit in the box';
   echo 'below to assist with verification.</p>';
   echo '<center><b><a id="editLink" target="_blank" href="' . ($useSecure ? 'https://' : 'http://') . 
      'en.wikipedia.org/wiki/Special:Mytalk?action=edit&section=new&preloadtitle=UTRS%20Account%20Request&preload=User:Hersfold/UTRSpreload' . 
      '">Edit your talk page to confirm your request</a></b></center>';
   echo '<center><small>(Link opens in a new window or tab)</small></center>';
   echo '<label id="diffLabel" for="diff" class="required">Confirmation diff:</label> <input id="diff" name="diff" type="text" value="' . $diff . '"/><br/><br/>';
   
   if (isset($publickey)) {
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

<p>By registering an account, you are consenting to allow us to collect and store your email
address and that you agree with the <a href="privacy.php">Privacy Policy</a>. We will not share
it with any third party. For more information, please see our <a href="privacy.php">Privacy Policy.</a>
<p><b>Warning: Do not use the Labs Project (this site) if you do not agree to the following: information shared with the Labs Project, including usernames and passwords, will be made available to volunteer administrators and may not be treated confidentially.</b>
<p>Volunteers may have full access to the systems hosting the projects, allowing them access to any data or other information you submit.
<p>As a result, use of your real Wikimedia credentials is highly discouraged in wmflabs.org projects. You should use a different password for your account than you would on projects like Wikipedia, Commons, etc.
<p>By creating an account in this project and/or using other Wikimedia Labs Services, you agree that the volunteer administrators of this project will have access to any data you submit.
<p>Since access to this information by volunteers is fundamental to the operation of Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.

<?php

   echo '<input type="submit" name="submit" value="Register"/>';
   echo '</form>';
   echo '</div></center>';
}

skinFooter();
?>
