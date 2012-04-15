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
require_once('template.php');

// make sure user is logged in, if not, kick them out
verifyLogin('appeal.php?id=' . $_GET['id']);

$error = null;

//Template header()
skinHeader();

if (!is_numeric($_GET['id'])) {
	throw new UTRSIllegalModificationException('Appeal id is not numeric.');
}

//construct appeal object
$appeal = Appeal::getAppealByID($_GET['id']);

//construct user object
$user = User::getUserByUsername($_SESSION['user']);

//construct log object
$log = Log::getCommentsByAppealId($_GET['id']);

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
				Log::ircNotification("\x033Appeal\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . $appeal->getID() . "\x033 ) reserved by \x032" . $_SESSION['user'] . "\x033 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 1);
			}
	} else {
		$error = "This request is already reserved or awaiting a checkuser or tool admin. If the person holding this ticket seems to be unavailable, ask a tool admin to break their reservation.";
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
					$log->addNewItem('Released appeal', 1);
					Log::ircNotification("\x033Appeal\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . $appeal->getID() . " \x033) released by \x032" . $_SESSION['user'] . "\x033 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 1);
				}
	} else {
		$error = "Cannot release hold on appeal";
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
					$appeal->setHandlingAdmin(null, 1);
					$log->addNewItem('Status change to AWAITING_CHECKUSER', 1);
			} else {
				$error = "Cannot set AWAITING_CHECKUSER status";
			}
			break;
		case "return":
			if (!(
				//Appeal is not in checkuser or admin status
				($appeal->getStatus() != Appeal::$STATUS_AWAITING_CHECKUSER && $appeal->getStatus() != Appeal::$STATUS_AWAITING_ADMIN) ||
				//Appeal is in checkuser status and user is not a checkuser or has the appeal assigned to them and not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && (!verifyAccess($GLOBALS['CHECKUSER']) || $appeal->getHandlingAdmin() != $user) ||
				//Appeal is in admin status and user is not admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
				//There is no old handling admin
				$appeal->getOldHandlingAdmin() == null ||
				//Appeal is closed and not an admin
				$appeal->getStatus() == Appeal::$STATUS_CLOSED
				)) {
					$appeal->setStatus(Appeal::$STATUS_AWAITING_REVIEWER);
					$appeal->returnHandlingAdmin();
					$log->addNewItem('Appeal reservation returned to ' . $appeal->getHandlingAdmin()->getUsername());
					$log->addNewItem('Status change to AWAITING_REVIEWER', 1);
			} else {
				$error = "Cannot return appeal to old handling tool user";
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
				$log->addNewItem('Status change to AWAITING_USER', 1);
			} else {
				$error = "Cannot assign AWAITING_USER status";
			}
			break;
		case "hold":
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
				$log->addNewItem('Status change to ON_HOLD', 1);
			} else {
				$error = "Cannot assign STATUS_ON_HOLD status";
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
				$log->addNewItem('Status change to AWAITING_PROXY', 1);
			} else {
				$error = "Cannot assign STATUS_AWAITING_PROXY status";
			}
			break;
		case "admin":
			if (!(
				//Already on awaiting admin
				$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN
				//Only condition to allow an appeal to be sent to awaiting admin for any reason
				)) {
				$appeal->setStatus(Appeal::$STATUS_AWAITING_ADMIN);
				$appeal->setHandlingAdmin(null, 1);
				$log->addNewItem('Status change to AWAITING_ADMIN', 1);
			} else {
				$error = "Cannot assign STATUS_AWAITING_ADMIN status";
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
				$log->addNewItem('Closed', 1);
			} else {
				$error = "Unable to close the appeal request";
			}
			break;
	}
	if (!$error) {
		Log::ircNotification("\x033Status changed for\x032 " . $appeal->getCommonName() . "\x033 (\x032 " . $appeal->getID() . "\x033 ) to \x032 " . $appeal->getStatus() . " \x033by \x032" . $_SESSION['user'] . "\x033 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 1);
		$appeal->update();
	}
}


//Log actions
if (isset($_GET['action']) && $_GET['action'] == "comment") {
	if (isset($_POST['comment'])) {
		$log->addNewItem(sanitizeText($_POST['comment']));
		Log::ircNotification("\x032" . $_SESSION['user'] . "\x033 has left a new comment on the appeal for\x032 " . $appeal->getCommonName() . "\x033 URL: " . getRootURL() . "appeal.php?id=" . $appeal->getID(), 1);
	} else {
		$error = "You have not entered a comment";
	}
}
?>
<script type="text/javascript">

var closeTemplate = 16;
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
	var response = confirm("Do you want to send a response to the user?")
	if (response) {
		window.location='sendEmail.php?tid=' + closeTemplate + '&id=<?php echo $_GET['id']; ?>';
	} else {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=close';
	}
}

function doAdmin() {
	var response = confirm("Do you really want to send this appeal to the tool admin queue?  Note: You will not be able to perform any other actions except comment until a tool admin can review it.  Please confirm this is really what you want to do.")
	if (response) {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=admin';
	} else {
		return false;
	}
}


function doCheckUser() {
	var response = confirm("Please confirm you want to send this appeal to the checkuser queue:")
	if (response) {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=checkuser';
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

?>
<h1>Details for Request #<?php echo $appeal->getID(); ?>: <a href="<?php echo getWikiLink($appeal->getUserPage(), $user->getUseSecure()); ?>" target="_blank"><?php echo $appeal->getCommonName(); ?></a> :: ******<?php echo substr($appeal->getEmail(), strpos($appeal->getEmail(), "@")); ?></h1>
<table class="appeal">
<tr>
<td valign=top class="left">
<div class="linklist">Account links:
<ul>
  <li><a href="<?php echo getWikiLink($appeal->getUserPage(), $user->getUseSecure()); ?>" target="_blank">User Page</a></li>
  <li><a href="<?php echo getWikiLink("User_talk:" . urlencode($appeal->getCommonName()), $user->getUseSecure()); ?>" target="_blank">User Talk Page</a></li>
  <li><a href="<?php echo getWikiLink("Special:Log/block", $user->getUseSecure(), "page=User:" . urlencode($appeal->getCommonName())); ?>" target="_blank">Block Log</a></li>
  <li><a href="<?php echo getWikiLink("Special:BlockList", $user->getUseSecure(), "wpTarget=" . urlencode($appeal->getCommonName()) . "&limit=50"); ?>" target="_blank">Find block</a></li> 
  <li><a href="<?php echo getWikiLink("Special:Contributions/" . urlencode($appeal->getCommonName()), $user->getUseSecure()); ?>" target="_blank">Contribs</a></li>
  <li><a href="<?php echo getWikiLink("Special:Unblock/" . urlencode($appeal->getCommonName()), $user->getUseSecure()); ?>" target="_blank">Unblock</a></li> 
  <li><a href="<?php echo getWikiLink("Special:UserLogin", $user->getUseSecure(), "type=signup"); ?>" target="_blank">Create Account</a></li>
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
<div class="linklist">Blocking Admin:
<ul>
  <li><a href="<?php echo getWikiLink("User:" . urlencode($appeal->getBlockingAdmin()), $user->getUseSecure()); ?>" target=\"_blank\"><?php echo $appeal->getBlockingAdmin(); ?></a></li>
  <li><a href="<?php echo getWikiLink("User_talk:" . urlencode($appeal->getBlockingAdmin()), $user->getUseSecure()); ?>" target=\"_blank\"> User talk Page</a></li>
  <li><a href="<?php echo getWikiLink("Special:EmailUser/" . urlencode($appeal->getBlockingAdmin()), $user->getUseSecure()); ?>" target=\"_blank\"> Email User</a></li>
  </ul>
  </div>
<?php if ($appeal->getHandlingAdmin()) {?>
<div class="linklist">Reserved by:
<ul>
<li><a href="userMgmt.php?userId=<?php echo $appeal->getHandlingAdmin()->getUserId(); ?>"><?php echo $handlingAdmin = $appeal->getHandlingAdmin()->getUsername(); ?></a></li> 
<li><a href="<?php echo getWikiLink("User:" . urlencode($appeal->getHandlingAdmin()->getWikiAccount()), $user->getUseSecure()); ?>" target=\"_blank\"> User Page</a></li> 
<li><a href="<?php echo getWikiLink("User_talk:" . urlencode($appeal->getHandlingAdmin()->getWikiAccount()), $user->getUseSecure()); ?>" target=\"_blank\"> User talk Page</a></li> 
<li><a href="<?php echo getWikiLink("Special:EmailUser/" . urlencode($appeal->getHandlingAdmin()->getWikiAccount()), $user->getUseSecure()); ?>" target=\"_blank\"> Email User</a></li>
</ul>
</div>
<?php } ?>
<?php if (verifyAccess($GLOBALS['CHECKUSER']) || verifyAccess($GLOBALS['DEVELOPER'])) {?>
<h3><a href="javascript:void(0)" onClick="showContextWindow('<?php echo sanitizeText($appeal->getIP() . " " . $appeal->getUserAgent()); ?>')">User Agent</a></h3>
<div class="info" style="height:60px !important;"><?php echo $appeal->getIP() . " " . $appeal->getUserAgent(); ?></div>
<?php }?>
<h3><a href="javascript:void(0)" onClick="showContextWindow('<?php echo sanitizeText($appeal->getAppeal()); ?>')">Why do you believe you should be unblocked?</a></h3>
<div class="info"><?php echo sanitizeText($appeal->getAppeal()); ?></div>
<h3><a href="javascript:void(0)" onClick="showContextWindow('<?php echo sanitizeText($appeal->getIntendedEdits()); ?>')">If you are unblocked, what articles do you intend to edit?</a></h3>
<div class="info"><?php echo sanitizeText($appeal->getIntendedEdits()); ?></div>
<h3><a href="javascript:void(0)" onClick="showContextWindow('<?php echo sanitizeText($appeal->getOtherInfo()); ?>')">Is there anything else you would like us to consider when reviewing your block?</a></h3>
<div class="info"><?php echo sanitizeText($appeal->getOtherInfo()); ?></div>
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
	//Checkuser button
	$disabled = "";
	if (
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
	//Return button
	$disabled = "";
	if (
		//Appeal is not in checkuser or admin status
		($appeal->getStatus() != Appeal::$STATUS_AWAITING_CHECKUSER && $appeal->getStatus() != Appeal::$STATUS_AWAITING_ADMIN) ||
		//Appeal is in checkuser status and user is not a checkuser or has the appeal assigned to them and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && (!verifyAccess($GLOBALS['CHECKUSER']) || $appeal->getHandlingAdmin() != $user) ||
		//Appeal is in admin status and user is not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
		//There is no old handling admin
		$appeal->getOldHandlingAdmin() == null ||
		//Appeal is closed and not an admin
		$appeal->getStatus() == Appeal::$STATUS_CLOSED
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Return\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=return'\">&nbsp;";
	//Awaiting user button
	$disabled = "";
	if (
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
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . " value=\"User\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=user'\">&nbsp;";
	echo "<hr style='width:200px;'>";
	//On Hold button
	$disabled = "";
	if (
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
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Hold\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=hold'\">&nbsp;";
	//Awaiting Proxy
	$disabled = "";
	if (
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
	echo "<input type=\"button\" " . $disabled . "  value=\"Proxy\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=proxy'\">&nbsp;";
	//Awaiting admin
	$disabled = "";
	if (
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
	<input type="button" value="Rangeblock" onClick="window.location='sendEmail.php?tid=11&id=<?php echo $_GET['id']; ?>'">&nbsp;-->
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
</div>
<script type="text/javascript">

$contextValue = "<?php echo mysql_real_escape_string($log->getLargeHTML()); ?>";

</script>
<h3><a href="javascript:void(0)" onClick="showContextWindow($contextValue)">Logs for this request</a> (<a href="comment.php?id=<?php echo $_GET['id']; ?>">new comment</a>)</h3>
<div class="comments">
<?php echo str_replace("\r\n", " ", $log->getSmallHTML()); ?>
</div>
<form action="?id=<?php echo $_GET['id']; ?>&action=comment" method="post"><input type="text" name="comment" style="width:75%;"><input type="submit" style="width:20%" value="Quick Comment"></form>

<?php if (verifyAccess($GLOBALS['ADMIN'])) {?>
<h3>Ban Management</h3>
<div style="text-align:center;">
<input type="button" value="Ban Email" onClick="window.location='banMgmt.php?appeal=<?php echo $_GET['id'];?>&target=0'">&nbsp;
<input type="button" value="Ban IP" onClick="window.location='banMgmt.php?appeal=<?php echo $_GET['id'];?>&target=1'">&nbsp;
<input type="button" value="Ban Username" onClick="window.location='banMgmt.php?appeal=<?php echo $_GET['id'];?>&target=2'">&nbsp;
</div>
<?php }?>
</td>
</tr>
</table>
</div>

<?php 

skinFooter();

?>
