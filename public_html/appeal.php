<?php
//TODO: Finish the conditionals on the action buttons
//TODO: Create new JS function for popups.  CU, Appeal, Other Info, and Log
//		will popup for easier viewing
//TODO: Finish the log

//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/appealObject.php');
require_once('src/userObject.php');
require_once('src/templateObj.php');
require_once('src/logObject.php');
require_once('src/messages.php');
require_once('src/emailTemplates.class.php');
require_once('template.php');
require_once('src/UTRSBot.class.php');

// make sure user is logged in, if not, kick them out
verifyLogin('appeal.php?id=' . $_GET['id']);

$error = null;
$errorMessages = '';
$lang = 'en';

//Template header()
skinHeader("	
	$(document).ready(function() {
		if ($(\"#adminhold\")) {
			$(\"#adminhold\").click(function() {
				varMessage = prompt(\"Please write a short message to be posted on-wiki for the blocking admin:\")
				window.location = '?id=" . $_GET['id'] . "&action=status&value=adminhold&adminmessage=' + varMessage;
			})
		}
	});
	");
try {	
	if (!is_numeric($_GET['id'])) {
		$text = SystemMessages::$error["AppealNotNumeric"][$lang];
		throw new UTRSIllegalModificationException($text);
	}
}
catch (UTRSIllegalModificationException $ex) {
   	  $errorMessages = $ex->getMessage() . $errorMessages;
}
if ($errorMessages) {
	displayError($errorMessages);
	skinFooter();
	die();
}

//construct appeal object
$appeal	= Appeal::getAppealByID($_GET['id']);

//construct user object
$user	= UTRSUser::getUserByUsername($_SESSION['user']);

//construct log object
$log	= Log::getCommentsByAppealId($_GET['id']);

if (verifyAccess($GLOBALS['CHECKUSER'])
		||verifyAccess($GLOBALS['OVERSIGHT'])
		||verifyAccess($GLOBALS['WMF'])
		||verifyAccess($GLOBALS['DEVELOPER'])) {
	if (isset($_POST['revealitem'])) {
		if (isset($_POST['revealcomment'])) {
			if ($_POST['revealitem'] == "cudata") {
				if (verifyAccess($GLOBALS['CHECKUSER'])||verifyAccess($GLOBALS['WMF'])) {
					$appeal->insertRevealLog($user->getUserId(), $_POST['revealitem']);
					$log->addNewItem("Revealed this appeals CU data: ".$_POST['revealcomment'], 1, TRUE);
				}
			}
			if ($_POST['revealitem'] == "email") {
				if (verifyAccess($GLOBALS['WMF'])||verifyAccess($GLOBALS['DEVELOPER'])) {
					$appeal->insertRevealLog($user->getUserId(), $_POST['revealitem']);
					$log->addNewItem("Revealed this appeals email: ".$_POST['revealcomment'], 1, TRUE);
				}
			}
			if ($_POST['revealitem'] == "oversightinfo") {
				if (verifyAccess($GLOBALS['OVERSIGHT'])||verifyAccess($GLOBALS['WMF'])) {
					$appeal->insertRevealLog($user->getUserId(), $_POST['revealitem']);
					$log->addNewItem("Revealed this appeals oversighted information: ".$_POST['revealcomment'], 1, TRUE);
				}
			}
		}
		else {
			$error = "No reveal reason was submitted. Please provide a reason.";
		}
	}
}
//Set the handling admin
if (isset($_GET['action']) && $_GET['action'] == "reserve"){
	if (!(
		//Already reserved
		$appeal->getHandlingAdmin() ||
		//Awaiting admin and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
		//Appeal awaiting CU and not CU or Admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
		//Appeal close and not admin
		$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
		)) {
			if (isset($_GET['user'])) {
				$success = $appeal->setHandlingAdmin($_GET['user']);
			} else {
				$success = $appeal->setHandlingAdmin($user->getUserId());
			}
			if ($success) {
				$appeal->update();
				$log->addNewItem('Reserved appeal', 1);
				Log::ircNotification("\x033Appeal\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . $appeal->getID() . "\x033 ) reserved by \x032" . $_SESSION['user'] . "\x033 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 0);
			}
	} else {
		$error = SystemMessages::$error['AppealReserved'][lang];
	}
}

if (isset($_GET['action']) && $_GET['action'] == "release"){
	if (!(
			//Not handling user and not admin
			$appeal->getHandlingAdmin() && $appeal->getHandlingAdmin()->getUserId() != $user->getUserId() && !verifyAccess($GLOBALS['ADMIN']) ||
			//In AWAITING_ADMIN status and not admin
			$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
			//Awaiting checkuser and not CU or admin
			$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
			//Appeal is closed and not an admin
			$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
			)) {
				$success = $appeal->setHandlingAdmin(null);
				if ($success) {
					$appeal->update();
					$log->addNewItem(SystemMessages::$log['AppealRelease'][$lang], 1);
					//TODO: Set IRC Multilingual
					Log::ircNotification("\x033Appeal\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . $appeal->getID() . " \x033) released by \x032" . $_SESSION['user'] . "\x033 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 0);
				}
	} else {
		$error = SystemMessages::$error['ReleaseFailed'][$lang];
	}
}
		
//Status change
if (isset($_GET['action']) && isset($_GET['value']) && $_GET['action'] == "status") {
	switch ($_GET['value']) {
		case "checkuser":
			if (!(
				//Awaiting checkuser (if it's already set to CU)
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//Assigned and not CU or Admin
				!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
				//Awaiting admin and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
				//Appeal is closed and not an admin
				$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
				)) {
					$appeal->setStatus(Appeal::$STATUS_AWAITING_CHECKUSER);
					$appeal->setHandlingAdmin(null, 1);//Need to temporarily break 
          if (isset($_GET['user'])) {
  				  $success = $appeal->setHandlingAdmin($_GET['user']);
          } else {
  				  $success = $appeal->setHandlingAdmin($user->getUserId());
          }
					$log->addNewItem(SystemMessages::$log['StatusToCU'][$lang], 1);
			} else {
				$error = SystemMessages::$log['CannotSetCU'][$lang];
			}
			break;
		case "return":		  
			if (!(
				//Appeal is not in checkuser or admin status
				($appeal->getStatus() != Appeal::$STATUS_AWAITING_CHECKUSER && $appeal->getStatus() != Appeal::$STATUS_AWAITING_ADMIN && $appeal->getStatus() != Appeal::$STATUS_AWAITING_PROXY  && $appeal->getStatus() != Appeal::$STATUS_ON_HOLD) ||
				//Appeal is in checkuser status and user is not a checkuser or has the appeal assigned to them and not admin
				($appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !verifyAccess($GLOBALS['CHECKUSER']) /*|| $appeal->getHandlingAdmin() != $user)*/) ||
				//Appeal is in admin status and user is not admin
				($appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN'])) ||
				//There is no old handling admin
				//($appeal->getOldHandlingAdmin() == null) ||
				//Appeal is closed and not an admin
				($appeal->getStatus() == Appeal::$STATUS_CLOSED)
				)) {
          //Mark CU as temp handler 
          /*if (isset($_GET['user'])) {
  				  $success = $appeal->setHandlingAdmin($_GET['user']);
          } else {
  				  $success = $appeal->setHandlingAdmin($user->getUserId());
          }
          if ($success) {
            $appeal->update();
          }                 */
          //End mark - Try no return
					$appeal->setStatus(Appeal::$STATUS_AWAITING_REVIEWER);
					//$appeal->returnHandlingAdmin();
					$log->addNewItem(SystemMessages::$log['AppealReturnUsers'][$lang]);
					$log->addNewItem(SystemMessages::$log['StatusAwaitReviewers'][$lang], 1);
					
					$admin = $appeal->getHandlingAdmin();
					//TODO: Set IRC Multilingual
					Log::ircNotification("\x033" . ($admin ? "Attention\x032 " . $admin->getUsername() . "\x033: " : "") . 
						"An appeal\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . 
						$appeal->getID() . " \x033) has been returned to you and the status has been updated to AWAITING_REVIEWER URL: " .
						getRootURL() . "appeal.php?id=" . $appeal->getID(), 0);
					
					//Email notification to the admin handling the appeal
					
					if ($admin->replyNotify()) {
						$email = $admin->getEmail();
						$headers = SystemMessages::$system['EmailFrom'][$lang];
						$headers .= SystemMessages::$system['EmailMIME'][$lang];
						$headers .= SystemMessages::$system['EmailContentType'][$lang];
						$body = SystemMessages::$system['AppealReturnEmail'][$lang].
								"<a href=\"" . getRootURL() . "appeal.php?id=" . $appeal->getID() . "\">" .
								SystemMessages::$system['ReviewResponse'][$lang]."</a>\n<hr />\n";
						$subject = SystemMessages::$system['EmailSubject'][$lang].$appeal->getID();
							
						$et = new EmailTemplates($admin, $appeal);
						$body = $et->apply_to($body);
			
						$body = str_replace("\n", "<br/>", $body);
							
						mail($email, $subject, $body, $headers);
					}
			} else {
				$error = SystemMessages::$error['FailReturnOldUser'][$lang];
			}
			break;
		case "user":
			if (!(
				//When it is already in STATUS_AWAITING_USER status
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_USER ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//When not handling user and not admin
				!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
				//In AWAITING_ADMIN status and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
				//Awaiting checkuser and not CU or admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
				//Appeal is closed and not an admin
				$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
				)) {
				$appeal->setStatus(Appeal::$STATUS_AWAITING_USER);
				$log->addNewItem(SystemMessages::$log['StatusAwaitUser'][$lang], 1);
			} else {
				$error = SystemMessages::$error['FailAwaitUser'][$lang];
			}
			break;
		case "hold":
		case "adminhold":
		case "wmfhold":
			if (!(
				//Already on hold
				$appeal->getStatus() == Appeal::$STATUS_ON_HOLD ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//When not handling user and not admin
				!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
				//In AWAITING_ADMIN status and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
				//Awaiting checkuser and not CU or admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
				//Appeal is closed and not an admin
				$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
				)) {
				$appeal->setStatus(Appeal::$STATUS_ON_HOLD);
				$log->addNewItem(SystemMessages::$log['StatusOnHold'][$lang], 1);
				
				//Notify the blocking admin, if asked for
				if ($_GET['value'] == "adminhold") {
					//Set up UTRSBot
					$bot = new UTRSBot();
					//Get current timestamp
					$time = date('M d, Y H:i:s', time());
					//Grab & sanitize admin message
					$adminmessage = (isset($_GET['adminmessage'])) ? sanitizeText($_GET['adminmessage']) : 'None specified';
					//Log admin message
					$log->addNewItem($adminmessage);
					//Post admin message on wiki
				    $bot->notifyAdmin($appeal->getCommonName(), array($appeal->getID(), $appeal->getCommonName(), $time, $appeal->getHandlingAdmin()->getUsername(), $adminmessage));	
					//Log that the admin was notified on-wiki
					$log->addNewItem(SystemMessages::$log['NotifiedAdmin'][$lang], 1);			
				} elseif ($_GET['value'] == "wmfhold") {
					$appeal->sendWMF();
					$log->addNewItem(SystemMessages::$log['NotifiedWMF'][$lang], 1);
				}
			} else {
				$error = SystemMessages::$error['FailOnHold'][$lang];
			}
			break;
		case "proxy":
			if (!(
				//Already on proxy
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_PROXY ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//When not handling user and not admin
				!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
				//In AWAITING_ADMIN status and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
				//Awaiting checkuser and not CU or admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
				//Appeal is closed and not an admin
				$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
				)) {
				$appeal->setStatus(Appeal::$STATUS_AWAITING_PROXY);
				$log->addNewItem(SystemMessages::$log['StatusAwaitProxy'][$lang], 1);
				
			    /* On Wiki Notifications */
				if (!$appeal->getAccountName() && !$appeal->hasAccount() && strlen($appeal->getIP()) < 32) {
					$bot = new UTRSBot();
					$time = date('M d, Y H:i:s', time());
					$bot->notifyOPP($appeal->getCommonName(), array($appeal->getCommonName(), "User has requested an unblock at {{utrs|" . $appeal->getID() . "}} and is in need of a proxy check."));
				} elseif ($appeal->getAccountName() && !$appeal->hasAccount()) {
					echo "<script type=\"text/javascript\"> alert(\"" . SystemMessages::$error['DivertToACC'][$lang] . "\"); </script>";
				} else {
					echo "<script type=\"text/javascript\"> alert(\"" . SystemMessages::$error['CannotPostOPP'][$lang] . "\"); </script>";
				}
			} else {
				$error = SystemMessages::$error['FailAwaitProxy'][$lang];
			}
			break;
		case "admin":
			if (!(
				//Already on awaiting admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN
				//Only condition to allow an appeal to be sent to awaiting admin for any reason
				)) {
				$appeal->setStatus(Appeal::$STATUS_AWAITING_ADMIN);
				//$appeal->setHandlingAdmin(null, 1);
				$log->addNewItem(SystemMessages::$log['StatusAwaitAdmin'][$lang], 1);
			} else {
				$error = SystemMessages::$error['FailAwaitAdmin'][$lang];
			}
			break;
		case "close":
			if (!(
				//When set to AWAITING_ADMIN and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
				//Not handling user and not admin
				$appeal->getHandlingAdmin() != $user && !verifyAccess($GLOBALS['ADMIN']) ||
				//When not assigned
				!($appeal->getHandlingAdmin()) ||
				//When closed
				$appeal->getStatus() == Appeal::$STATUS_CLOSED
				)) {
				$appeal->setStatus(Appeal::$STATUS_CLOSED);
				$log->addNewItem(SystemMessages::$log['AppealClosed'][$lang], 1);
			} else {
				$error = SystemMessages::$error['FailCloseAppeal'][$lang];
			}
			break;
	case "invalid":
			if (
				//admin
				verifyAccess($GLOBALS['DEVELOPER']) &&
				//When assigned
				($appeal->getHandlingAdmin() === NULL)
				)
			 {
					$appeal->setStatus(Appeal::$STATUS_INVALID);
					$log->addNewItem('Appeal has been scrapped.', 1);
			} else {
					//TODO: Why is this have a verify access call on the end? what does it print?
					//TODO: --DQ 27/5/15
					$error = SystemMessages::$error['FailInvalid'][$lang];
				}
				break;
    case "new":
			if (
				//already here, don't do it again @TParis *caugh*
				$appeal->getStatus() != Appeal::$STATUS_NEW &&
				//admin
				verifyAccess($GLOBALS['ADMIN']) &&
				//When assigned
				($appeal->getHandlingAdmin() === NULL)
				) {
				$appeal->setStatus(Appeal::$STATUS_NEW);
				$log->addNewItem('Reset appeal to NEW', 1);
			} else {
				//TODO: Why is this have a verify access call on the end? what does it print?
				//TODO: --DQ 27/5/15
				$error = SystemMessages::$error['FailResetAppeal'][$lang]." - ".verifyAccess($GLOBALS['ADMIN']);
			}
			break;
	}
	if (!$error) {
		Log::ircNotification("\x033Status changed for\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . $appeal->getID() . "\x033 ) to \x032 " . $appeal->getStatus() . " \x033by \x032" . $_SESSION['user'] . "\x033 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 0);
		$appeal->update();
	}
}


//Log actions
if (isset($_GET['action']) && $_GET['action'] == "comment") {
	if (isset($_POST['comment'])) {
		$log->addNewItem(sanitizeText($_POST['comment']));
		Log::ircNotification("\x032" . $_SESSION['user'] . "\x033 has left a new comment on the appeal for\x032 " . $appeal->getCommonName() . "\x033 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 0);
	} else {
		$error = SystemMessages::$error['NoCommentProvided'];
	}
}
?>
<script type="text/javascript">
//TODO: @Bug 42 - Does this actually show up anywhere on the interface? 
var actionsContextWindow = "<b>Reserve</b> - <i>This button reserves the appeal under your login.  Reserving allows to access to other buttons as well as the ability to respond to the appeal.</i><br><br>" +
						   "<b>Release</b> - <i>Release removes your name from the appeal.  It allows other users to reserve the appeal.  Note: You will lose the ability to respond to this appeal.</i><br><br>" +
						   "<b>Checkuser</b> - <i>Assigns the current status to the checkuser queue.  You will lose your reservation of the appeal.</i><br><br>" +
						   "<b>Return</b> - <i>Assigns the ticket back to the user who submitted it to Checkusers or Tool Admins.  Also sets the ticket to AWAITING_REVIEWER status.</i><br><br>" +
						   "<b>User</b> - <i>Assigns the status to awaiting the appealant to respond (automatically set by some templates)</i><br><br>" +
						   "<b>Hold</b> - <i>Assigns the status to hold.  Use this when discussing with a blocking admin or any other reason where the request is still being considered but awaiting another action</i><br><br>" +
						   "<b>Proxy</b> - <i>Awaiting a response from WP:OPP</i><br><br>" +
						   "<b>Tool Admin</b> - <i>This button is always available.  It assigns the request to a tool admin.  Use to open closed requests or to get an appeal released if the reserved user has gone AWOL</i><br><br>" +
						   "<b>Close</b> - <i>This button closes the appeal.  All buttons will be disabled.</i>"						   
function doClose() {
	var response = confirm("<?php echo SystemMessages::$system['ConfirmClose'][$lang]; ?>")
	if (response) {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=close';
	}
}

function doAdmin() {
	var response = confirm("<?php echo SystemMessages::$system['ConfirmAdmin'][$lang]; ?>")
	if (response) {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=admin';
	} else {
		return false;
	}
}


function doCheckUser() {
	var response = confirm("<?php echo SystemMessages::$system['ConfirmCU'][$lang]; ?>")
	if (response) {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=checkuser';
	} else {
		return false;
	}
}
function doInvalid() {
	var response = confirm("Please confirm you want revoke this appeal:")
	if (response) {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=invalid';
	} else {
		return false;
	}
}

function doNew() {
	var response = confirm("Please confirm you want to send this appeal to the new queue:")
	if (response) {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=new';
	} else {
		return false;
	}
}
function showContextWindow(innerContent) {
	myWindow = document.getElementById('contextWindow');
	myContent = document.getElementById('contextContent');
	myWindow.style.visibility = 'visible';
	myContent.innerHTML = innerContent;	
}

function hideContextWindow() {
	myWindow = document.getElementById('contextWindow');
	myContent = document.getElementById('contextContent');
	myWindow.style.visibility = 'hidden';
	myContent.innerHTML = '';	
}

</script>
<div id='contextWindow'>
	<div id='contextHeader'><a href="javascript:void(0)" onClick="hideContextWindow()">X</a></div>
	<div id='contextContent'></div>
</div>
<div id='appealContent'>
<?php 
if (isset($_GET['action'])) {
	if ($error) {
		displayError($error);
	}
}

if (
		($appeal->getStatus() != Appeal::$STATUS_UNVERIFIED) 
		|| verifyAccess($GLOBALS['DEVELOPER'])) {
?>
<h1>Details for Request #<?php echo $appeal->getID(); ?>: <a href="<?php echo getWikiLink($appeal->getUserPage(), $user->getUseSecure()); ?>" target="_blank"><?php echo $appeal->getCommonName(); ?></a> - <?php
if (!verifyAccess($GLOBALS['WMF'])&&!verifyAccess($GLOBALS['DEVELOPER'])){
	echo "******";
	echo substr($appeal->getEmail(), strpos($appeal->getEmail(), "@")); 
}
else {
	if ($appeal->checkRevealLog($user->getUserId(), "email")) {
		echo $appeal->getEmail();
	}
	else {
		echo "******";
		echo substr($appeal->getEmail(), strpos($appeal->getEmail(), "@"));
	}
}?>
</h1>
<table class="appeal">
<tr>
<td valign=top class="left">
<div class="linklist">Account links:
<ul>
  <li><a href="<?php echo getWikiLink($appeal->getUserPage(), $user->getUseSecure()); ?>" target="_blank">User Page</a></li>
  <li><a href="<?php echo getWikiLink("User_talk:" . $appeal->getCommonName(), $user->getUseSecure()); ?>" target="_blank">User Talk Page</a></li>
  <li><a href="<?php echo getWikiLink("Special:Log/block", $user->getUseSecure(), array('page' => "User:" . $appeal->getCommonName())); ?>" target="_blank">Block Log</a></li>
  <li><a href="<?php echo getWikiLink("Special:BlockList", $user->getUseSecure(), array('wpTarget' => $appeal->getCommonName(), 'limit' => '50')); ?>" target="_blank">Find block</a></li> 
  <li><a href="<?php echo getWikiLink("Special:Contributions/" . $appeal->getCommonName(), $user->getUseSecure()); ?>" target="_blank">Contribs</a></li>
  <li><a href="<?php echo getWikiLink("Special:Unblock/" . $appeal->getCommonName(), $user->getUseSecure()); ?>" target="_blank">Unblock</a></li> 
  <!-- <li><a href="<?php //echo getWikiLink("Special:UserLogin", $user->getUseSecure(), array('type'=>"signup")); ?>" target="_blank">Create Account</a></li> We are unable to create accounts right now--> 
</ul>
</div>
Request timestamp: <?php echo $appeal->getTimestamp(); ?><br>
<?php if (!$appeal->hasAccount() && $appeal->getAccountName()) {?>
Requested Username: <a href="<?php echo getWikiLink("User:" . $appeal->getAccountName(), $user->getUseSecure()); ?>" target="_blank"><?php echo $appeal->getAccountName(); ?></a><br>
<?php }?>
<?php if (Appeal::getAppealCountByIP($appeal->getIP()) > 1) {?>
Appeals by this IP: <a href="search.php?id=<?php echo $appeal->getID(); ?>"><b><?php echo Appeal::getAppealCountByIP($appeal->getIP()); ?></b></a><br>
<?php }?>
Status: <b><?php echo $appeal->getStatus(); ?></b><br>
Requesting unblock for: <b><?php if ($appeal->isAutoblock() && $appeal->hasAccount()) {echo "IP Address/Autoblock underneath an account";} elseif ($appeal->hasAccount()) {echo "Account";} elseif (!$appeal->hasAccount()) {echo "IP Address";} else {throw new UTRSValidationException("No Appeal type specified");}?></b>
<div class="linklist">Blocking Admin:
<ul>
  <li><a href="<?php echo getWikiLink("User:" . $appeal->getBlockingAdmin(), $user->getUseSecure()); ?>" target=\"_blank\"><?php echo $appeal->getBlockingAdmin(); ?></a></li>
  <li><a href="<?php echo getWikiLink("User_talk:" . $appeal->getBlockingAdmin(), $user->getUseSecure()); ?>" target=\"_blank\"> User talk Page</a></li>
  <li><a href="<?php echo getWikiLink("Special:EmailUser/" . $appeal->getBlockingAdmin(), $user->getUseSecure()); ?>" target=\"_blank\"> Email User</a></li>
  </ul>
  </div>
<?php if ($appeal->getHandlingAdmin()) {?>
<div class="linklist">Reserved by:
<ul>
<li><a href="userMgmt.php?userId=<?php echo $appeal->getHandlingAdmin()->getUserId(); ?>"><?php echo $handlingAdmin = $appeal->getHandlingAdmin()->getUsername(); ?></a></li> 
<li><a href="<?php echo getWikiLink("User:" . $appeal->getHandlingAdmin()->getWikiAccount(), $user->getUseSecure()); ?>" target=\"_blank\"> User Page</a></li> 
<li><a href="<?php echo getWikiLink("User_talk:" . $appeal->getHandlingAdmin()->getWikiAccount(), $user->getUseSecure()); ?>" target=\"_blank\"> User talk Page</a></li> 
<li><a href="<?php echo getWikiLink("Special:EmailUser/" . $appeal->getHandlingAdmin()->getWikiAccount(), $user->getUseSecure()); ?>" target=\"_blank\"> Email User</a></li>
</ul>
</div>
<?php }
if (verifyAccess($GLOBALS['CHECKUSER']) || verifyAccess($GLOBALS['WMF'])) {
	?>
<h3>User Agent</h3>
<div class="info" style="height:60px !important;"><?php 
if ($appeal->checkRevealLog($user->getUserId(), "cudata")) {
		echo $appeal->getIP() . " " . $appeal->getUserAgent();
	}
	else {
		echo "<b><font color=\"red\">Access denied. You need to submit a reveal request in the bottom right.</font></b>";
	}?></div>
<?php }?>


<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode(nl2br($appeal->getAppeal()))); ?>)">Why do you believe you should be unblocked?</a></h3>
<div class="info"><?php echo nl2br(htmlspecialchars($appeal->getAppeal())); ?></div>
<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode(nl2br($appeal->getIntendedEdits()))); ?>)">If you are unblocked, what articles do you intend to edit?</a></h3>
<div class="info"><?php echo nl2br(htmlspecialchars($appeal->getIntendedEdits())); ?></div>
<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode(nl2br($appeal->getBlockReason()))); ?>)">Why do you think there is a block currently affecting you? If you believe it's in error, tell us how.</a></h3>
<div class="info"><?php echo nl2br(htmlspecialchars($appeal->getBlockReason())); ?></div>
<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode(nl2br($appeal->getOtherInfo()))); ?>)">Is there anything else you would like us to consider when reviewing your block?</a></h3>
<div class="info"><?php echo nl2br(htmlspecialchars($appeal->getOtherInfo())); ?></div>
<br>
</td>
<td valign=top class="right">
<h3><a href="javascript:void(0)" onClick="showContextWindow(actionsContextWindow);">Actions</a></h3>
<div style="text-align:center;">
	<?php
	
	// This section affects the action buttons
	
	$disabled = "";
	// Reserve and release buttons
	if ($appeal->getHandlingAdmin()) {
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
			//Not handling user and not admin
			$appeal->getHandlingAdmin()->getUserId() != $user->getUserId() && !verifyAccess($GLOBALS['ADMIN']) ||
			//In AWAITING_ADMIN status and not admin
			$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
			//Awaiting checkuser and not CU or admin
			$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
			//Appeal is closed and not an admin
			$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
			) {
			$disabled = " disabled = 'disabled' ";
		}
		echo "<input type=\"button\" " . $disabled . " value=\"Release\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=release'\">&nbsp;";
	} else {
		if (
				//When it is already in INVALID status
				$appeal->getStatus() == Appeal::$STATUS_INVALID ||
			//Awaiting admin and not admin
			$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
			//Appeal awaiting CU and not CU or Admin
			$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
			//Appeal close and not admin
			$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
		) {
			$disabled = " disabled = 'disabled' ";
		}
		echo "<input type=\"button\" " . $disabled . " value=\"Reserve\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=reserve'\">&nbsp;";
	}
  //New button
	$disabled = "";
	if (
			//When it is already in INVALID status and not a dev
			($appeal->getStatus() == Appeal::$STATUS_INVALID && !verifyAccess($GLOBALS['DEVELOPER'])) ||
		//Awaiting new
		$appeal->getStatus() == Appeal::$STATUS_NEW ||
		//When is assigned
		($appeal->getHandlingAdmin()) ||
		//Assigned and not CU or Admin
		!verifyAccess($GLOBALS['ADMIN']) ||
		//Awaiting admin and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_PROXY ||
		//Appeal is closed and not an admin
		$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Reset to new\" onClick=\"doNew()\">&nbsp;";
	//Return button
	$disabled = "";
	if (
			//When it is already in INVALID status
			$appeal->getStatus() == Appeal::$STATUS_INVALID ||
		//Appeal needs to be reserved to send back to an admin
		!($appeal->getHandlingAdmin()) ||
		//Appeal is not in checkuser or admin status
		($appeal->getStatus() != Appeal::$STATUS_AWAITING_CHECKUSER && $appeal->getStatus() != Appeal::$STATUS_AWAITING_ADMIN && $appeal->getStatus() != Appeal::$STATUS_AWAITING_PROXY  && $appeal->getStatus() != Appeal::$STATUS_ON_HOLD) ||
		//Appeal is in checkuser status and user is not a checkuser or has the appeal assigned to them and not admin
		($appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !verifyAccess($GLOBALS['CHECKUSER']) /*|| $appeal->getHandlingAdmin() != $user*/) ||
    //For above, no one really cares if your the active handling admin for reviewing a CU req...
		//Appeal is in admin status and user is not admin
		($appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN'])) ||
		//If it is in the proxy queue, allow through
		//!($appeal->getStatus() == Appeal::$STATUS_AWAITING_PROXY) ||
		//There is no old handling admin - Not going to work, i've mod'd the comment to not require the old admin
		//$appeal->getOldHandlingAdmin() == null ||
		//Appeal is closed and not an admin
		($appeal->getStatus() == Appeal::$STATUS_CLOSED)
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Back to Reviewing admin\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=return'\">&nbsp;";
	//Awaiting user button
	$disabled = "";
	if (
			//When it is already in INVALID status
			$appeal->getStatus() == Appeal::$STATUS_INVALID ||
		//When it is already in STATUS_AWAITING_USER status
	    $appeal->getStatus() == Appeal::$STATUS_AWAITING_USER ||
	    //When not assigned
	    !($appeal->getHandlingAdmin()) ||
	    //When not handling user and not admin
	    !($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
	    //In AWAITING_ADMIN status and not admin
	    ($appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN'])) ||
	    //Awaiting checkuser and not CU or admin
	    ($appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER']))) ||
	    //Appeal is closed and not an admin
	    ($appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN']))
	    ) {
	    $disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . " value=\"Await Response\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=user'\">&nbsp;";
	//Invalid button
	$disabled = "";
	if (
			//When it is already in INVALID status
			$appeal->getStatus() == Appeal::$STATUS_INVALID ||
			//When not dev
			!verifyAccess($GLOBALS['DEVELOPER'])
	) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . " value=\"Invalid\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=invalid'\">&nbsp;";
  //Checkuser button
	$disabled = "";
	if (
			//When it is already in INVALID status
			$appeal->getStatus() == Appeal::$STATUS_INVALID ||
		//Awaiting checkuser (if it's already set to CU)
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER ||
		//When not assigned
		!($appeal->getHandlingAdmin()) ||
		//Assigned and not CU or Admin
		!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
		//Awaiting admin and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
		//Appeal is closed and not an admin
		$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Checkuser\" onClick=\"doCheckUser()\">&nbsp;";
	echo "<hr style='width:475px;'>";
	//On Hold button
	$disabled = "";
	if (
			//When it is already in INVALID status
			$appeal->getStatus() == Appeal::$STATUS_INVALID ||
		//Already on hold
		$appeal->getStatus() == Appeal::$STATUS_ON_HOLD ||
		//When not assigned
		!($appeal->getHandlingAdmin()) ||
		//When not handling user and not admin
		(!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN']))) ||
		//In AWAITING_ADMIN status and not admin
		($appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN'])) ||
		//Awaiting checkuser and not CU or admin
		($appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER']))) ||
		//Appeal is closed and not an admin
		($appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN']))
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Request a Hold\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=hold'\">&nbsp;";
	echo "<input type=\"button\" " . $disabled . "  value=\"Blocking Admin\" id=\"adminhold\">&nbsp;";
	echo "<input type=\"button\" " . $disabled . "  value=\"WMF Staff\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=wmfhold'\">&nbsp;";
	//Awaiting Proxy
	$disabled = "";
	if (
			//When it is already in INVALID status
			$appeal->getStatus() == Appeal::$STATUS_INVALID ||
		//Already on proxy
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_PROXY ||
		//When not assigned
		!($appeal->getHandlingAdmin()) ||
		//When not handling user and not admin
		!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
		//In AWAITING_ADMIN status and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
		//Awaiting checkuser and not CU or admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
		//Appeal is closed and not an admin
		$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['ADMIN'])
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Request Proxy Check\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=proxy'\">&nbsp;";
	//Awaiting admin
	$disabled = "";
	if (
			//When it is already in INVALID status
			$appeal->getStatus() == Appeal::$STATUS_INVALID ||
		//Already on awaiting admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN
		//Only condition to allow an appeal to be sent to awaiting admin for any reason
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Tool Admin\" onClick=\"doAdmin()\">&nbsp;";
	//Close button
	$disabled = "";
	if (
			//When it is already in INVALID status
			$appeal->getStatus() == Appeal::$STATUS_INVALID ||
		//When set to AWAITING_ADMIN and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
		//Not handling user and not admin
		$appeal->getHandlingAdmin() != $user && !verifyAccess($GLOBALS['ADMIN']) ||
		//When not assigned
		!($appeal->getHandlingAdmin()) ||
		//When closed
		$appeal->getStatus() == Appeal::$STATUS_CLOSED
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . " value=\"Close\" onClick=\"doClose();\">";
	?>
</div>
<h3>Responses</h3>
<div style="text-align:center;">
<!-- 
	<input type="button" value="Username" onClick="window.location='sendEmail.php?tid=7&id=<?php echo $_GET['id']; ?>'">&nbsp;
	<input type="button" value="Need Info" onClick="window.location='sendEmail.php?tid=9&id=<?php echo $_GET['id']; ?>'">&nbsp;
	<input type="button" value="School" onClick="window.location='sendEmail.php?tid=21&id=<?php echo $_GET['id']; ?>'">&nbsp;
	<input type="button" value="Rangeblock" onClick="window.location='sendEmail.php?tid=11&id=<?php echo $_GET['id']; ?>'">&nbsp; 
-->
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
<?php if (verifyAccess($GLOBALS['CHECKUSER'])||verifyAccess($GLOBALS['OVERSIGHT'])||verifyAccess($GLOBALS['DEVELOPER'])||verifyAccess($GLOBALS['WMF'])) {
	$higherPerms = TRUE;
}
else {$higherPerms = FALSE;}?>
<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode($log->getLargeHTML($higherPerms))) ?>)">Logs for this request</a> (<a href="comment.php?id=<?php echo $_GET['id']; ?>">new comment</a>)</h3>
<div class="comments">
<?php echo str_replace("\r\n", " ", $log->getSmallHTML($higherPerms)); ?>
</div>
<form action="?id=<?php echo $_GET['id']; ?>&action=comment" method="post">
<input type="text" name="comment" id="quickComment" style="width:75%;" onblur="sizeAudit('quickComment','sizeQuickComment',10000)"><input type="submit" style="width:20%" value="Quick Comment" id="quickSubmit">
<p id="sizeQuickComment"></p>
</form>

<?php if (verifyAccess($GLOBALS['ADMIN'])) {?>
<h3>Ban Management</h3>
<div style="text-align:center;">
<input type="button" value="Ban Email" onClick="window.location='banMgmt.php?appeal=<?php echo $_GET['id'];?>&target=0'">&nbsp;
<input type="button" value="Ban IP" onClick="window.location='banMgmt.php?appeal=<?php echo $_GET['id'];?>&target=1'">&nbsp;
<input type="button" value="Ban Username" onClick="window.location='banMgmt.php?appeal=<?php echo $_GET['id'];?>&target=2'">&nbsp;
</div>
<?php }?>
<?php if (verifyAccess($GLOBALS['OVERSIGHT'])||verifyAccess($GLOBALS['WMF'])||verifyAccess($GLOBALS['DEVELOPER'])||verifyAccess($GLOBALS['CHECKUSER'])) {?>
<h3>Reveal Management</h3>
<div style="text-align:center;"><form action="?id=<?php echo $_GET['id']; ?>&action=reveal" method="post">
<input type="radio" name="revealitem" value="email" <?php if (!verifyAccess($GLOBALS['WMF'])&&!verifyAccess($GLOBALS['DEVELOPER'])) {echo "disabled='disabled'";} ?>><label for="email">Email Address</label>
<input type="radio" name="revealitem" value="cudata" <?php if (!verifyAccess($GLOBALS['WMF'])&&!verifyAccess($GLOBALS['DEVELOPER'])&&!verifyAccess($GLOBALS['CHECKUSER'])) {echo "disabled='disabled'";} ?>><label for="cudata">CU data</label>
<input type="radio" name="revealitem" value="oversightinfo" <?php if (!verifyAccess($GLOBALS['WMF'])&&!verifyAccess($GLOBALS['DEVELOPER'])&&!verifyAccess($GLOBALS['OVERSIGHT'])) {echo "disabled='disabled'";} ?>><label for="email">Oversighted Appeal Information - No current affect</label>
<input type="text" name="revealcomment" style="width:75%;"><input type="submit" style="width:20%" value="Reveal"></form>
</div>
<?php }?>
</td>
</tr>
</table>
</div>
</div>


<?php 
}
elseif ($appeal->getStatus() == Appeal::$STATUS_INVALID) {
	displayError("You may not view appeals that have been marked invalid by a developer.");
}
else {
	displayError("You may not view appeals that have not been email verified.");
}
?>
<script type="text/javascript">
function sizeAudit(item,name,max) {
	var size = document.getElementById(item).value.length;
	if(size>max){
		document.getElementById(item).style.border = "thin solid #FF0000";
		document.getElementById(name).innerHTML = "You have inputed too much content into the above text box. Please reduce to "+max+" charecters.";
		document.getElementById(name).style.color = "#FF0000";
		document.getElementById(name).style.background = "#FFFFFF";
		document.getElementById("quickSubmit").disabled = true;
	}
	else {
		document.getElementById(item).style.border = "none none #FF0000";
		document.getElementById(name).innerHTML = "";
		document.getElementById(name).style.color = "#FFFFFF";
		document.getElementById(name).style.background = "none";
		document.getElementById("quickSubmit").disabled = false;
	}
}
</script>
<?php
skinFooter();


?>
