<?php
//TODO: Finish the conditionals on the action buttons
//TODO: Create new JS function for popups.  CU, Appeal, Other Info, and Log
//		will popup for easier viewing
//TODO: Finish the log

//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('../src/userObject.php');
require_once('../src/templateObj.php');
require_once('template.php');

// make sure user is logged in, if not, kick them out
verifyLogin('appeal.php?id=' . $_GET['id']);

$errorMessages = '';

//Template header()
skinHeader();

if (!is_numeric($_GET['id'])) {
	throw new UTRSIllegalModificationException('Appeal id is not numeric.');
}

//construct appeal object
$appeal = Appeal::getAppealByID($_GET['id']);

//construct user object
$user = User::getUserByUsername($_SESSION['user']);

//Set the handling admin
if (isset($_GET['action']) && $_GET['action'] == "reserve"){
	if (isset($_GET['user'])) {
		$appeal->setHandlingAdmin($_GET['user']);
	} else {
		$appeal->setHandlingAdmin($user->getUserId());
	}
	$appeal->update();
}

if (isset($_GET['action']) && $_GET['action'] == "release"){
	$appeal->setHandlingAdmin(null);
	$appeal->update();
}

//Status change
if (isset($_GET['action']) && isset($_GET['value']) && $_GET['action'] == "status") {
	switch ($_GET['value']) {
		case "checkuser":
			$appeal->setStatus(Appeal::$STATUS_AWAITING_CHECKUSER);
			$appeal->setHandlingAdmin(null);
			break;
		case "user":
			$appeal->setStatus(Appeal::$STATUS_AWAITING_USER);
			break;
		case "hold":
			$appeal->setStatus(Appeal::$STATUS_ON_HOLD);
			break;
		case "proxy":
			$appeal->setStatus(Appeal::$STATUS_AWAITING_PROXY);
			break;
		case "admin":
			$appeal->setStatus(Appeal::$STATUS_AWAITING_ADMIN);
			$appeal->setHandlingAdmin(null);
			break;
		case "close":
			$appeal->setStatus(Appeal::$STATUS_CLOSED);
			break;
	}
	$appeal->update();
}
?>
<script language="Javascript" type="text/javascript">

function doClose() {
	var response = confirm("Do you want to send a response to the user?")
	if (response) {
		window.location='sendEmail.php?tid=22&id=<?php echo $_GET['id']; ?>';
	} else {
		window.location='?id=<?php echo $_GET['id']; ?>&action=status&value=close';
	}
}

</script>
<div id='appealContent'>
<h1>Details for Request #<?php echo $appeal->getID(); ?>: <a href="<?php echo getWikiLink($appeal->getUserPage(), $user->getUseSecure()); ?>" target="_new"><?php echo $appeal->getCommonName(); ?></a></h1>
<table class="appeal">
<tr>
<td valign=top class="left">
Account links: <a href="<?php echo getWikiLink($appeal->getUserPage(), $user->getUseSecure()); ?>" target="_new">User Page</a> | <a href="<?php echo getWikiLink("Special:Block/" . $appeal->getCommonName(), $user->getUseSecure()); ?>" target="_new">Block Log</a> | <a href="<?php echo getWikiLink("Special:Contributions/" . $appeal->getCommonName(), $user->getUseSecure()); ?>" target="_new">Contribs</a><br>
Request timestamp: <?php echo $appeal->getTimestamp(); ?><br>
Status: <b><?php echo $appeal->getStatus(); ?></b><br>
<?php if ($appeal->getHandlingAdmin()) {?>
Assigned: <?php $handlingAdmin = $appeal->getHandlingAdmin(); echo $handlingAdmin->getUsername(); $handlingAdmin = null; ?><br>
<?php } ?>
<?php if (verifyAccess($GLOBALS['CHECKUSER']) || verifyAccess($GLOBALS['ADMIN'])) {?>
<h3>User Agent</h3>
<div class="useragent"><?php echo $appeal->getUserAgent(); ?></div>
<?php }?>
<h3>Appeal</h3>
<div class="info"><?php echo $appeal->getAppeal(); ?></div>
<h3>Other Info</h3>
<div class="info"><?php echo $appeal->getOtherInfo(); ?></div>
<br>
</td>
<td valign=top class="right">
<h3>Actions</h3>
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
			$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN'] || verifyAccess($GLOBALS['CHECKUSER']))) ||
			//Appeal is closed and not an admin
			$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['admin'])
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
		//Assigned and not CU or Admin
		!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
		//Awaiting admin and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN'])
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Checkuser\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=checkuser'\">&nbsp;";
	//Awaiting user button
	$disabled = "";
	if (
		//When it is already in STATUS_AWAITING_USER status
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_USER ||
		//When not handling user and not admin
		!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
		//In AWAITING_ADMIN status and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
		//Awaiting checkuser and not CU or admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
		//Appeal is closed and not an admin
		$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['admin'])
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . " value=\"User\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=user'\">&nbsp;";
	//On Hold button
	$disabled = "";
	if (
		//Already on hold
		$appeal->getStatus() == Appeal::$STATUS_ON_HOLD ||
		//When not handling user and not admin
		!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
		//In AWAITING_ADMIN status and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
		//Awaiting checkuser and not CU or admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
		//Appeal is closed and not an admin
		$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['admin'])
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . "  value=\"Hold\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=hold'\">&nbsp;";
	//Awaiting Proxy
	$disabled = "";
	if (
		//Already on proxy
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_PROXY ||
		//When not handling user and not admin
		!($appeal->getHandlingAdmin() == $user || verifyAccess($GLOBALS['ADMIN'])) ||
		//In AWAITING_ADMIN status and not admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_ADMIN && !verifyAccess($GLOBALS['ADMIN']) ||
		//Awaiting checkuser and not CU or admin
		$appeal->getStatus() == Appeal::$STATUS_AWAITING_CHECKUSER && !(verifyAccess($GLOBALS['ADMIN']) || verifyAccess($GLOBALS['CHECKUSER'])) ||
		//Appeal is closed and not an admin
		$appeal->getStatus() == Appeal::$STATUS_CLOSED && !verifyAccess($GLOBALS['admin'])
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
	echo "<input type=\"button\" " . $disabled . "  value=\"Admin\" onClick=\"window.location='?id=" . $_GET['id'] . "&action=status&value=admin'\">&nbsp;";
	//Close button
	$disabled = "";
	if (
		//Not handling user and not admin
		$appeal->getHandlingAdmin() != $user && !verifyAccess($GLOBALS['ADMIN'])
		) {
		$disabled = "disabled='disabled'";
	}
	echo "<input type=\"button\" " . $disabled . " value=\"Close\" onClick=\"doClose();\">";
	?>
</div>
<h3>Responses</h3>
<div style="text-align:center;">
	<input type="button" value="Username" onClick="window.location='sendEmail.php?tid=7&id=<?php echo $_GET['id']; ?>'">&nbsp;
	<input type="button" value="Need Info" onClick="window.location='sendEmail.php?tid=9&id=<?php echo $_GET['id']; ?>'">&nbsp;
	<input type="button" value="School" onClick="window.location='sendEmail.php?tid=21&id=<?php echo $_GET['id']; ?>'">&nbsp;
	<input type="button" value="Rangeblock" onClick="window.location='sendEmail.php?tid=11&id=<?php echo $_GET['id']; ?>'">&nbsp;
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
<h3>Logs for this request (<a href="comment.php?id=<?php echo $_GET['id']; ?>">new comment</a>)</h3>
<div class="comments">
</div>
<form><input type="text" style="width:75%;"><input type="submit" style="width:20%" value="Quick Comment"></form>
</td>
</tr>
</table>


</div>

<?php 

skinFooter();

?>