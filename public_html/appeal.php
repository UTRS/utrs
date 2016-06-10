<?php
//TODO: Finish the conditionals on the action buttons
//TODO: Create new JS function for popups.  CU, Appeal, Other Info, and Log
//		will popup for easier viewing
//TODO: Finish the log

//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/languageCookie.php');
echo checkCookie();
$lang=getCookie();
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
					$log->addNewItem(SystemMessages::$log['RevealCUData'][$lang].$_POST['revealcomment'], 1, TRUE);
				}
			}
			if ($_POST['revealitem'] == "email") {
				if (verifyAccess($GLOBALS['WMF'])||verifyAccess($GLOBALS['DEVELOPER'])) {
					$appeal->insertRevealLog($user->getUserId(), $_POST['revealitem']);
					$log->addNewItem(SystemMessages::$log['RevealEmail'][$lang].$_POST['revealcomment'], 1, TRUE);
				}
			}
			if ($_POST['revealitem'] == "oversightinfo") {
				if (verifyAccess($GLOBALS['OVERSIGHT'])||verifyAccess($GLOBALS['WMF'])) {
					$appeal->insertRevealLog($user->getUserId(), $_POST['revealitem']);
					$log->addNewItem(SystemMessages::$log['RevealOS'][$lang].$_POST['revealcomment'], 1, TRUE);
				}
			}
		}
		else {
			$error = SystemMessages::$error['NoRevealReason'][$lang];
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
				$log->addNewItem(SystemMessages::$log['AppealReserved'][$lang], 1);
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
					$bot->notifyOPP($appeal->getCommonName(), array($appeal->getCommonName(), ['']." {{".['']."|" . $appeal->getID() . "}} ".['']));
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
					$log->addNewItem([''] , 1);
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
				$log->addNewItem([''], 1);
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
		$error = SystemMessages::$error['NoCommentProvided'][$lang];
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
	var response = confirm("<?php echo [''] ?>")
	if (response) {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=invalid';
	} else {
		return false;
	}
}

function doNew() {
	var response = confirm("<?php echo [''] ?>")
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
<h1><?php echo SystemMessages::$system['DetailsReqNum'][$lang]?><?php echo $appeal->getID(); ?>: <a href="<?php echo getWikiLink($appeal->getUserPage(), $user->getUseSecure()); ?>" target="_blank"><?php echo $appeal->getCommonName(); ?></a> - <?php
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
<div class="linklist"><?php echo SystemMessages::$links['AcctLinks'][$lang]?>
<ul>
  <li><a href="<?php echo getWikiLink($appeal->getUserPage(), $user->getUseSecure()); ?>" target="_blank"><?php echo SystemMessages::$system['Upage'][$lang]?></a></li>
  <li><a href="<?php echo getWikiLink("User_talk:" . $appeal->getCommonName(), $user->getUseSecure()); ?>" target="_blank"><?php echo SystemMessages::$links['UTPageNoLink']?></a></li>
  <li><a href="<?php echo getWikiLink("Special:Log/block", $user->getUseSecure(), array('page' => [''] . $appeal->getCommonName())); ?>" target="_blank"><?php echo SystemMessages::$links['BlkLog'][$lang]?></a></li>
  <li><a href="<?php echo getWikiLink("Special:BlockList", $user->getUseSecure(), array('wpTarget' => $appeal->getCommonName(), 'limit' => '50')); ?>" target="_blank"><?php echo SystemMessages::$system['FindBlk'] ?></a></li> 
  <li><a href="<?php echo getWikiLink("Special:Contributions/" . $appeal->getCommonName(), $user->getUseSecure()); ?>" target="_blank">SystemMessages::$system['Crontribs'][$lang]</a></li>
  <li><a href="<?php echo getWikiLink("Special:Unblock/" . $appeal->getCommonName(), $user->getUseSecure()); ?>" target="_blank">Unblock</a></li> 
  <!-- <li><a href="<?php //echo getWikiLink("Special:UserLogin", $user->getUseSecure(), array('type'=>"signup")); ?>" target="_blank">Create Account</a></li> We are unable to create accounts right now--> 
</ul>
</div>
<?php echo SystemMessages::$system['Timestamp'][$lang]?> <?php echo $appeal->getTimestamp(); ?><br>
<?php if (!$appeal->hasAccount() && $appeal->getAccountName()) {?>
<?php echo SystemMessages::$information['RequestUname'][$lang]?> <a href="<?php echo getWikiLink([''] . $appeal->getAccountName(), $user->getUseSecure()); ?>" target="_blank"><?php echo $appeal->getAccountName(); ?></a><br>
<?php }?>
<?php if (Appeal::getAppealCountByIP($appeal->getIP()) > 1) {?>
<?php echo SystemMessages::$system['AppealByIP'][$lang]?> <a href="search.php?id=<?php echo $appeal->getID(); ?>"><b><?php echo Appeal::getAppealCountByIP($appeal->getIP()); ?></b></a><br>
<?php }?>
<?php echo SystemMessages::$system['Status'][$lang]?> <b><?php echo $appeal->getStatus(); ?></b><br>
<?php echo SystemMessages::$system['ReqUnblockFor'][$lang] ?><b><?php if ($appeal->isAutoblock() && $appeal->hasAccount()) {echo SystemMessages::$system['IPorAuto'][$lang];} elseif ($appeal->hasAccount()) {echo SystemMessages::$system['Acct'][$lang];} elseif (!$appeal->hasAccount()) {echo SystemMessages::$system['IP'][$lang];;} else {throw new UTRSValidationException(SystemMessages::$error['NoneType'][$lang]);}?></b>
<div class="linklist"><?php SystemMessages::$system['BlkAdmin'][$lang]?>
<ul>
  <li><a href="<?php echo getWikiLink([''] . $appeal->getBlockingAdmin(), $user->getUseSecure()); ?>" target=\"_blank\"><?php echo $appeal->getBlockingAdmin(); ?></a></li>
  <li><a href="<?php echo getWikiLink("User_talk:" . $appeal->getBlockingAdmin(), $user->getUseSecure()); ?>" target=\"_blank\"> User talk Page</a></li>
  <li><a href="<?php echo getWikiLink(['']  . $appeal->getBlockingAdmin(), $user->getUseSecure()); ?>" target=\"_blank\"> SystemMessages::$system['EmailUser'][$lang]</a></li>
  </ul>
  </div>
<?php if ($appeal->getHandlingAdmin()) {?>
<div class="linklist">SystemMessages::$system['ReservedBy'][$lang]
<ul>
<li><a href="userMgmt.php?userId=<?php echo $appeal->getHandlingAdmin()->getUserId(); ?>"><?php echo $handlingAdmin = $appeal->getHandlingAdmin()->getUsername(); ?></a></li> 
<li><a href="<?php echo getWikiLink([''] . $appeal->getHandlingAdmin()->getWikiAccount(), $user->getUseSecure()); ?>" target=\"_blank\"> SystemMessages::$system['Upage'][$lang]</a></li> 
<li><a href="<?php echo getWikiLink("User_talk:" . $appeal->getHandlingAdmin()->getWikiAccount(), $user->getUseSecure()); ?>" target=\"_blank\"> User talk Page</a></li> 
<li><a href="<?php echo getWikiLink(['']  . $appeal->getHandlingAdmin()->getWikiAccount(), $user->getUseSecure()); ?>" target=\"_blank\"> SystemMessages::$system['EmailUser'][$lang]</a></li>
</ul>
</div>
<?php }
if (verifyAccess($GLOBALS['CHECKUSER']) || verifyAccess($GLOBALS['WMF'])) {
	?>
<h3>SystemMessages::$system['UserAgent'][$lang]</h3>
<div class="info" style="height:60px !important;"><?php 
if ($appeal->checkRevealLog($user->getUserId(), "cudata")) {
		echo $appeal->getIP() . " " . $appeal->getUserAgent();
	}
	else {
		echo "<b><font color=\"red\">".['']."</font></b>";
	}?></div>
<?php }?>


<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode(nl2br($appeal->getAppeal()))); ?>)">SystemMessages::$system['WhyUnblock'][$lang]</a></h3>
<div class="info"><?php echo nl2br(htmlspecialchars($appeal->getAppeal())); ?></div>
<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode(nl2br($appeal->getIntendedEdits()))); ?>)">SystemMessages::$system['IntendEdit'][$lang]</a></h3>
<div class="info"><?php echo nl2br(htmlspecialchars($appeal->getIntendedEdits())); ?></div>
<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode(nl2br($appeal->getBlockReason()))); ?>)">SystemMessages::$system['AffectYou'][$lang]</a></h3>
<div class="info"><?php echo nl2br(htmlspecialchars($appeal->getBlockReason())); ?></div>
<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode(nl2br($appeal->getOtherInfo()))); ?>)">SystemMessages::$system['AnyConsider'][$lang]</a></h3>
<div class="info"><?php echo nl2br(htmlspecialchars($appeal->getOtherInfo())); ?></div>
<br>
</td>
<td valign=top class="right">
<h3><a href="javascript:void(0)" onClick="showContextWindow(actionsContextWindow);">Actions</a></h3>
<div style="text-align:center;">
	<?php
	
	// This section affects the action buttons
	echo '<div class="btn-toolbar" role="toolbar" id="tool-row">';
	// Reserve and release buttons
	echo StatusButtonChecks::checkReserveRelease($appeal,$user);
	//SystemMessages::$links['ChangeStatus'][$lang]?>
	<div class="btn-group">
		<button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">SystemMessages::$links['ChangeStatus'][$lang] <span class="caret"></span></button>
		<ul class="dropdown-menu" role="menu"><?php 
			//New button
			echo StatusButtonChecks::checkNew($appeal,$user);
			//Return button
			echo StatusButtonChecks::checkReturn($appeal,$user);
			//Invalid button
			echo StatusButtonChecks::checkInvalid($appeal,$user);
			//Awaiting user button
			echo StatusButtonChecks::checkAwaitUser($appeal,$user);
			//On Hold button
			echo StatusButtonChecks::checkHold($appeal,$user);
    	?></ul>
    </div>
    <?php //Defer group?>
    <div class="btn-group">
		<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">SystemMessages::$links['Requests'][$lang] <span class="caret"></span></button>
		<ul class="dropdown-menu" role="menu"><?php 
			//Checkuser button
			echo StatusButtonChecks::checkCheckuser($appeal,$user);
			//Awaiting Proxy
			echo StatusButtonChecks::checkAwaitProxy($appeal,$user);
			//Awaiting admin
			echo StatusButtonChecks::checkAwaitAdmin($appeal,$user);
    	?></ul>
    </div>
	<?php 
	//Close button
	echo StatusButtonChecks::checkClose($appeal,$user);
	?>
</div>
<h3>SystemMessages::$system['Responses'][$lang]</h3>
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
				echo "<option>".SystemMessages::$error['NoTempAvail'][$lang]."</option>";
			} else {
				echo "<option value='-1'>".SystemMessages::$system['Select'][$lang]."</option>";
				
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
<h3><a href="javascript:void(0)" onClick="showContextWindow(<?php echo htmlspecialchars(json_encode($log->getLargeHTML($higherPerms))) ?>)">SystemMessages::$system['LogsBReq'][$lang]</a> (<a href="comment.php?id=<?php echo $_GET['id']; ?>">SystemMessages::$system['NewComment'][$lang]</a>)</h3>
<div class="comments">
<?php echo str_replace("\r\n", " ", $log->getSmallHTML($higherPerms)); ?>
</div>
<form action="?id=<?php echo $_GET['id']; ?>&action=comment" method="post"><input type="text" name="comment" style="width:75%;"><input type="submit" style="width:20%" value=['']></form>

<?php if (verifyAccess($GLOBALS['ADMIN'])) {?>
<h3>SystemMessages::$system['BanMgmt'][$lang]</h3>
<div style="text-align:center;">
<input type="button" value=[''] onClick="window.location='banMgmt.php?appeal=<?php echo $_GET['id'];?>&target=0'">&nbsp;
<input type="button" value=[''] onClick="window.location='banMgmt.php?appeal=<?php echo $_GET['id'];?>&target=1'">&nbsp;
<input type="button" value=[''] onClick="window.location='banMgmt.php?appeal=<?php echo $_GET['id'];?>&target=2'">&nbsp;
</div>
<?php }?>
<?php if (verifyAccess($GLOBALS['OVERSIGHT'])||verifyAccess($GLOBALS['WMF'])||verifyAccess($GLOBALS['DEVELOPER'])||verifyAccess($GLOBALS['CHECKUSER'])) {?>
<h3>SystemMessages::$system['RevealMgmt'][$lang]</h3>
<div style="text-align:center;"><form action="?id=<?php echo $_GET['id']; ?>&action=reveal" method="post">
<input type="radio" name="revealitem" value="email" <?php if (!verifyAccess($GLOBALS['WMF'])&&!verifyAccess($GLOBALS['DEVELOPER'])) {echo "disabled='disabled'";} ?>><label for="email">SystemMessages::$system['EmailAddr'][$lang]</label>
<input type="radio" name="revealitem" value="cudata" <?php if (!verifyAccess($GLOBALS['WMF'])&&!verifyAccess($GLOBALS['DEVELOPER'])&&!verifyAccess($GLOBALS['CHECKUSER'])) {echo "disabled='disabled'";} ?>><label for="cudata">SystemMessages::$system['CU data'][$lang]</label>
<input type="radio" name="revealitem" value="oversightinfo" <?php if (!verifyAccess($GLOBALS['WMF'])&&!verifyAccess($GLOBALS['DEVELOPER'])&&!verifyAccess($GLOBALS['OVERSIGHT'])) {echo "disabled='disabled'";} ?>><label for="email">SystemMessages::$system['OversightInfo'][$lang]</label>
<input type="text" name="revealcomment" style="width:75%;"><input type="submit" style="width:20%" value=['']></form>
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
	displayError(['']);
}
else {
	displayError(['']);
}
skinFooter();


?>
