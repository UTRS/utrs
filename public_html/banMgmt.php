<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/userObject.php');
require_once('src/statsLib.php');
require_once('src/banObject.php');
require_once('src/appealObject.php');
require_once('src/logObject.php');
require_once('src/messages.php');
require_once('template.php');
require_once('sitemaintain.php');

checkOnline();

verifyLogin('banMgmt.php');

$errors = '';

$target = null;
$displayTarget = null;

try{
	if(!verifyAccess($GLOBALS['ADMIN'])){
		throw new UTRSCredentialsException(SystemMessages::$error['TooladminsOnlyBan'][$lang]);
	}

	// set target if link followed from appeals page
	if(isset($_GET['appeal'])){
		$appeal = Appeal::getAppealByID($_GET['appeal']);
		if(strcmp($_GET['target'], "1") == 0){
			$target = $appeal->getIP();
			$displayTarget = md5($target);
			$type = "IP";
		}else if(strcmp($_GET['target'], "2") == 0){
			$target = $appeal->getAccountName();
			$displayTarget = $target;
			$type = "account name";
		}else{
			$target = $appeal->getEmail();
			$displayTarget = $target;
			$type = "email";
		}
	}

	// create ban & redirect if new ban form submitted
	if(isset($_POST['submit'])){
		// if not manually entered, grab from above switch
		if(!isset($_POST['target'])){
			$_POST['target'] = $target;
		}
		else{
			if(preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $_POST['target'])){
				$type = "IP";
			}
			else if(validEmail($_POST['target'])){
				$type = "email";
			}
			else{
				$type = "account name";
			}
		}

		// handles all validation, spits out exceptions if there's a problem
		$ban = new Ban($_POST);

		Log::ircNotification("\x033A new " . $type . " ban has been created by \x032" . $_SESSION['user'] .
			"\x033 Expires:\x032 " . ($ban->getExpiry() ? $ban->getExpiry() : "Indefinite"));

		// revert to view / delete screen
		header("Location: " . getRootURL() . "banMgmt.php?id=" . $ban->getBanID());
	}

	// delete ban
	if(isset($_POST['delete'])){
		$id = $_GET['id'];
		$ban = Ban::getBanByID($id);
		$ban->delete();

		Log::ircNotification("\x033Ban #" . $id . " has been deleted by \x032" . $_SESSION['user']);

		header("Location: " . getRootURL() . "banMgmt.php?delete=true");
	}
}
catch(UTRSException $e){
	$errors = $e->getMessage();
}

skinHeader('', true);

?>

<h2>Ban management</h2>

<?php
if($errors){
	displayError($errors);
}

if(verifyAccess($GLOBALS['ADMIN'])){
// new ban form
if(isset($_GET['new']) || isset($_GET['appeal'])){
	$postArgs = "";
	if(isset($_GET['new'])){
		$postArgs = "?new=true";
	}
	else{
		$postArgs = "?appeal=" . $_GET['appeal'] . "&target=" . $_GET['target'];
	}
	$duration = null;
	$unit = null;
	$reason = null;
	if(isset($_POST['durationAmt'])){
		$duration = $_POST['durationAmt'];
	}
	if(isset($_POST['durationUnit'])){
		$unit = $_POST['durationUnit'];
	}
	if(isset($_POST['reason'])){
		$reason = $_POST['reason'];
	}
	if($target == null && isset($_POST['target'])){
		$target = $_POST['target'];
	}
?>
	<form name="newBan" id="newBan" method="POST" action="banMgmt.php<?php echo $postArgs; ?>" style="white-space:normal !important;">
		<table style="background:none; border:none;" cellpadding="4px">
			<tr>
				<td class="required">Target:</td>
				<td>
					<?php if(isset($_GET['appeal'])){ echo (verifyAccess($GLOBALS['DEVELOPER']) ? $displayTarget : censorEmail($target)); }else{?>
					<input name="target" id="target" type="text" value="<?php echo $target;?>" />
					<?php } // closes else from if($target)?>
				</td>
			</tr>
			<tr>
				<td>Duration (leave blank for indefinite): </td>
				<td>
					<input name="durationAmt" id="durationAmt" type="text" value="<?php echo $duration;?>" /> <select name="durationUnit" id="durationUnit">
						<option value=""></option>
						<option value="seconds" <?php if($unit == "seconds"){echo "selected=\"selected\"";}?>>Second(s)</option>
						<option value="minutes" <?php if($unit == "minutes"){echo "selected=\"selected\"";}?>>Minute(s)</option>
						<option value="hours" <?php if($unit == "hours"){echo "selected=\"selected\"";}?>>Hour(s)</option>
						<option value="days" <?php if($unit == "days"){echo "selected=\"selected\"";}?>>Day(s)</option>
						<option value="weeks" <?php if($unit == "weeks"){echo "selected=\"selected\"";}?>>Week(s)</option>
						<option value="months" <?php if($unit == "months"){echo "selected=\"selected\"";}?>>Month(s)</option>
						<option value="years" <?php if($unit == "years"){echo "selected=\"selected\"";}?>>Year(s)</option>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top" class="required">Reason: </td>
				<td><textarea name="reason" id="reason" rows="4" cols="40"><?php echo $reason; ?></textarea></td>
			</tr>
			<tr>
				<td><input name="submit" id="submit" type="submit" value="Submit" /></td>
				<td></td>
			</tr>
		</table>
	</form>
<?php
} // closes if(isset($_GET['new']) || isset($_GET['appeal']))
// display / delete form
else if(isset($_GET['id'])){
	$ban = Ban::getBanByID($_GET['id']);
?>
	<table style="background:none; border:none;" cellpadding="4px">
		<tr>
			<th>Ban ID</th>
			<td><?php echo $ban->getBanID(); ?></td>
		</tr>
		<tr>
			<th>Target</th>
			<td><?php echo (verifyAccess($GLOBALS['DEVELOPER']) ? $ban->getTarget() : censorEmail($ban->getTarget())); ?></td>
		</tr>
		<tr>
			<th>Set on</th>
			<td><?php echo $ban->getTimestamp(); ?></td>
		</tr>
		<tr>
			<th>Expires</th>
			<td><?php if($ban->getExpiry()){echo $ban->getExpiry();}else{echo "Indefinite";} ?></td>
		</tr>
		<tr>
			<th>Set by</th>
			<td><?php echo $ban->getAdmin()->getUsername(); ?></td>
		</tr>
		<tr>
			<th>Reason</th>
			<td><?php echo $ban->getReason(); ?></td>
		</tr>
	</table>
	<form name="deleteBan" id="deleteBan" method="POST" action="banMgmt.php?id=<?php echo $ban->getBanID(); ?>">
		<input type="submit" name="delete" id="delete" value="Delete Ban" />
	</form>
<?php
} // close else if(isset($_GET['id]']))
// ban list
else{
	echo "<p><a href=\"" . getRootURL() . "banMgmt.php?new=true\">Set a new ban</a></p>\n\n";

	if(isset($_GET['delete'])){
		displaySuccess("Ban deleted.");
	}

	$bans = Ban::getAllActiveBans();
	if($bans){
		$i = 0;

		echo "<table class=\"appealList\">";
		echo "\t<tr><th>ID</th><th>Target</th><th>Expires</th><th>View</th></tr>";
		foreach( $bans as $ban ){
			if ($i++ % 2) {
				$rowformat = "even";
			} else {
				$rowformat = "odd";
			}
			echo "\t<tr class=\"". $rowformat . "\">\n";
			echo "\t\t<td>" . $ban->getBanID() . "</td>\n";
			echo "\t\t<td>" . (verifyAccess($GLOBALS['DEVELOPER']) ? $ban->getTarget() : censorEmail($ban->getTarget())) . "</td>\n";
			echo "\t\t<td>" . ($ban->getExpiry() ? $ban->getExpiry() : "Indefinite") . "</td>\n";
			echo "\t\t<td><a href=\"" . getRootURL() . "banMgmt.php?id=" . $ban->getBanID() . "\" style=\"color:green\">View</a></td>\n";
			echo "\t</tr>\n";
		}
		echo "</table>";
	}
	else{
		echo "<p><b>There are no active bans currently in the system.</b></p>";
	}

} // close else

} //close verifyAccess(admin)

skinFooter();
?>
