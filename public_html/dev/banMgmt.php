<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/userObject.php');
require_once('../src/statsLib.php');
require_once('../src/banObject.php');
require_once('../src/appealObject.php');
require_once('template.php');

verifyLogin('banMgmt.php');

$errors = '';

$target = null;

try{
	if(!verifyAccess($GLOBALS['ADMIN'])){
		throw new UTRSCredentialsException("Ban management is limited to tool administrators.");
	}
	
	// set target if link followed from appeals page
	if(isset($_GET['appeal'])){
		$appeal = Appeal::getAppealByID($_GET['appeal']);
		switch($_GET['target']){
			case "1": $target = $appeal->getIP();
			case "2": $target = $appeal->getAccountName();
			default: $target = $appeal->getEmail();
		}
	}

	// create ban & redirect if new ban form submitted
	if(isset($_POST['submit'])){
		// if not manually entered, grab from above switch 
		if(!isset($_POST['target'])){
			$_POST['target'] = $target;
		}

		// handles all validation, spits out exceptions if there's a problem
		$ban = new Ban($_POST);
		
		// revert to view / delete screen
		header("Location: " . getRootURL() . "banMgmt.php?id=" . $ban->getBanID());
	}
	
	// delete ban
	if(isset($_POST['delete'])){
		$id = $_GET['id'];
		$ban = Ban::getBanByID($id);
		$ban->delete();
		
		header("Location: " . getRootURL() . "banMgmt.php?delete=true");
	}
}
catch(UTRSException $e){
	$errors = $e->getMessage();
}

skinHeader();

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
?>
	<form name="newBan" id="newBan" method="POST" action="banMgmt.php<?php echo $postArgs; ?>">
		<table style="background:none; border:none;" cellpadding="4px">
			<tr>
				<td class="required">Target:</td>
				<td>
					<?php if($target){ echo (verifyAccess($GLOBALS['DEVELOPER']) ? $target : censorEmail($target)); }else{?>
					<input name="target" id="target" type="text" value="<?php echo $_POST['target'];?>" />
					<?php } // closes else from if($target)?>
				</td>
			</tr>
			<tr>
				<td>Duration (leave blank for indefinite): </td>
				<td>
					<input name="durationAmt" id="durationAmt" type="text" value="<?php echo $_POST['durationAmt'];?>" />
					<select name="durationUnit" id="durationUnit">
						<option value="seconds" <?php if($_POST['durationUnit'] == "seconds"){echo "selected=\"selected\"";}?>>Second(s)</option>
						<option value="minutes" <?php if($_POST['durationUnit'] == "minutes"){echo "selected=\"selected\"";}?>>Minute(s)</option>
						<option value="hours" <?php if($_POST['durationUnit'] == "hours"){echo "selected=\"selected\"";}?>>Hour(s)</option>
						<option value="days" <?php if($_POST['durationUnit'] == "days"){echo "selected=\"selected\"";}?>>Day(s)</option>
						<option value="weeks" <?php if($_POST['durationUnit'] == "weeks"){echo "selected=\"selected\"";}?>>Week(s)</option>
						<option value="months" <?php if($_POST['durationUnit'] == "months"){echo "selected=\"selected\"";}?>>Month(s)</option>
						<option value="years" <?php if($_POST['durationUnit'] == "years"){echo "selected=\"selected\"";}?>>Year(s)</option>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top" class="required">Reason: </td>
				<td><textarea name="reason" id="reason" rows="4" cols="40"><?php echo $_POST['reason']; ?></textarea></td>
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
	
} // close else

} //close verifyAccess(admin)

skinFooter();
?>