<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/userObject.php');
require_once('src/statsLib.php');
require_once('src/logObject.php');
require_once('template.php');

verifyLogin('userMgmt.php');

$errors = '';

// form processing up here to force log out if needed
if(isset($_GET['userId']) & isset($_POST['submit']) & verifyAccess($GLOBALS['ADMIN'])){
	$user = getCurrentUser();
	$userId = $_GET['userId'];
	$requestedUser = User::getUserById($userId);
	$approved = $requestedUser->isApproved();
	$active = $requestedUser->isActive();
	$admin = $requestedUser->isAdmin();
	$checkuser = $requestedUser->isCheckuser();
	$developer = $requestedUser->isDeveloper();
	$comments = $requestedUser->getComments();

	try{
		$newApproved = isset($_POST['approved']);
		$newActive = isset($_POST['active']);
		$newComments = $_POST['comments'];
		$newAdmin = isset($_POST['admin']);
		$newDeveloper = isset($_POST['developer']);
		$newCheckuser = isset($_POST['checkuser']);
			
		// check required fields
		if(!$approved & !$newApproved){
			throw new UTRSIllegalModificationException("You must approve this account in order to " .
				            "make any other access changes.");
		}
		if(!$newActive & !$newComments){
			throw new UTRSIllegalModificationException("You must provide a reason why this account " .
					        "has been deactivated.");
		}
		// check credentials (unlikely someone will spoof the POST header, but just in case)
		if(($newCheckuser != $checkuser) & (!$user->isCheckuser() | !$user->isDeveloper())){
			throw new UTRSIllegalModificationException("You lack sufficient permission to make these " .
					        "changes. The checkuser flag may only be changed by developers who also have the " .
					        "checkuser flag.");
		}
		if(($newDeveloper != $developer) & !$user->isDeveloper()){
			throw new UTRSIllegalModificationException("You lack sufficient permission to make these " .
					        "changes. The developer flag may only be changed by other developers.");
		}
		// carry out changes
		if(!$approved & $newApproved){
			$requestedUser->approve($user);
			Log::ircNotification("\x032,0 " . $requestedUser->getUsername() . "\x033,0's account has been approved by\x032,0 " . $_SESSION['user']);
		}
		if($active & !$newActive){
			$requestedUser->disable($user, $newComments);
			Log::ircNotification("\x032,0 " . $requestedUser->getUsername() . "\x033,0's account has been disabled by\x032,0 " . $_SESSION['user']);
		}
		else if(!$active & $newActive){
			$requestedUser->enable($user);
			Log::ircNotification("\x032,0 " . $requestedUser->getUsername() . "\x033,0's account has been enabled by\x032,0 " . $_SESSION['user']);
		}
		if(($newAdmin != $admin) | ($newDeveloper != $developer) | ($newCheckuser != $checkuser)){
			$requestedUser->setPermissions($newAdmin, $newDeveloper, $newCheckuser, $user);
			Log::ircNotification("\x032,0 " . $requestedUser->getUsername() . "\x033,0's permissioned have been updated by\x032,0 " . $_SESSION['user'] . ".  Tool Admin: " . (string)$newAdmin . " Tool Developer: " . (string)$newDeveloper . " Checkuser:" . (string)$newCheckuser);
		}
		// reset current user
		$user = getCurrentUser();
	}
	catch(UTRSException $e){
		$errors = $e->getMessage();
	}
}
else if(isset($_GET['userId']) & isset($_POST['rename']) & verifyAccess($GLOBALS['ADMIN'])){
	$user = getCurrentUser();
	$userId = $_GET['userId'];
	$requestedUser = User::getUserById($userId);
	$newName = $_POST['newName'];
	
	try{
		if(!isset($_POST['newName']) || !$newName){
			throw new UTRSIllegalModificationException("You must provide a new username in order to rename this user.");
		}
		if(strpos($newName, "#") !== false | strpos($newName, "/") !== false |
		   strpos($newName, "|") !== false | strpos($newName, "[") !== false |
		   strpos($newName, "]") !== false | strpos($newName, "{") !== false |
		   strpos($newName, "}") !== false | strpos($newName, "<") !== false |
		   strpos($newName, ">") !== false | strpos($newName, "@") !== false |
		   strpos($newName, "%") !== false | strpos($newName, ":") !== false | 
		   strpos($newName, '$') !== false){
		   	throw new UTRSIllegalModificationException('The username you have entered is invalid. Usernames ' .
		   	 	'may not contain the characters # / | [ ] { } < > @ % : $');
		}
		try{
			$existingUser = User::getUserByUsername($newName);
			// if no exception, then there's a problem
			throw new UTRSIllegalModificationException("Another user already has the name \"" . $newName . 
				"\". Please enter another username.");
		}
		catch(UTRSDatabaseException $e){
			if(strpos($e->getMessage(), "No results were returned") !== false){
				// that's good
			}
			else{
				// that's not good
				throw $e;
			}
		}
		
		$requestedUser->renameUser($newName, $user);
	}
	catch(UTRSException $e){
		$errors = $e->getMessage();
	}

}

// in case user modified their own access, make sure we don't need to log them out
verifyLogin('userMgmt.php');

skinHeader("function toggleRequired() {
	var label = document.getElementById('commentsLabel');
	if(label.className == 'required'){
		label.className = '';
	}
	else{
		label.className='required';
	}
}

function setRequired(required) {
	var label = document.getElementById('commentsLabel');
	if(!required){
		label.className = '';
	}
	else{
		label.className='required';
	}
}");


echo "<h2>User management</h2>";

if(!verifyAccess($GLOBALS['ADMIN'])){
	displayError("<b>Access denied:</a> User management is only available to tool administrators. "
	    . "Please click on one of the links above to return to another page.");
}
else{
	
	if(isset($_GET['userId'])){
		$user = getCurrentUser();
		$secure = $user->getUseSecure();
		$userId = $_GET['userId'];
		$requestedUser = User::getUserById($userId);
		$approved = $requestedUser->isApproved();
		$active = $requestedUser->isActive();
		$admin = $requestedUser->isAdmin();
		$checkuser = $requestedUser->isCheckuser();
		$developer = $requestedUser->isDeveloper();
		$comments = $requestedUser->getComments();
		$registered = $requestedUser->getRegistered();
		$numClosed = $requestedUser->getClosed();
		$wikiAccount = "User:" . $requestedUser->getWikiAccount();
		
		echo "<h3>" . $requestedUser->getUsername() . "</h3>";
		if($errors){
			displayError($errors);
		}
		else if(isset($_POST['submit'])){
			displaySuccess("Account successfully updated.");
		}
?>

<table style="border:none; background: none;">
	<tr>
		<td style="width:40%" valign="top">
			<table style="border: none; background: none;">
				<tr>
					<th style="text-align: left">User ID:</th>
					<td><?php echo $userId; ?>
					</td>
				</tr>
				<tr>
					<th style="text-align: left">Wikipedia account:</th>
					<td><a href="<?php echo getWikiLink($wikiAccount, $secure); ?>"><?php echo $wikiAccount; ?>
					</a>
					</td>
				</tr>
				<tr>
					<th style="text-align: left">Number of closed appeals:</th>
					<td><?php echo $numClosed; ?>
					</td>
				</tr>
				<tr>
					<th style="text-align: left">Registered:</th>
					<td><?php echo $registered; ?> UTC</td>
				</tr>
			</table>

			<h4>Access levels</h4>
			<table>
<?php 
echo "<form name=\"accessControl\" id=\"accessControl\" method=\"POST\" action=\"userMgmt.php?userId=" . $userId . "\">\n";
// if not approved, require that the account be approved before any other changes are made
if(!$approved){
	echo "<tr><td><label name=\"approvedLabel\" id=\"approvedLabel\" for=\"approved\" class=\"required\">Approve this account: " .
		 "</label></td> &#09; <td><input type=\"checkbox\" name=\"approved\" id=\"approved\" />\n</td></tr>";	
}
echo "<tr><td><label name=\"activeLabel\" id=\"activeLabel\" for=\"active\">Activate account:</label> </td><td>&#09; <input name=\"active\" " .
     "id=\"active\" type=\"checkbox\" onchange=\"toggleRequired()\" " . ($active ? "checked=\"checked\"" : "" ) . " />\n</td></tr>";
echo "<tr><td colspan=2><label name=\"commentsLabel\" id=\"commentsLabel\" " . (!$active ? "class=\"required\"" : "") . " for=\"comments\" " .
	 " />Reason for deactivating this account:</label>\n";
echo "<textarea name=\"comments\" id=\"comments\" rows=\"3\" cols=\"30\" />" . $comments . "</textarea>\n</td></tr>";
echo "<tr><td><label name=\"adminLabel\" id=\"adminLabel\" for=\"admin\">Tool administrator:</label> </td><td>&#09; <input name=\"admin\" " .
	 "id=\"admin\" type=\"checkbox\" " . ($admin ? "checked=\"checked\"" : "") . " />\n</td></tr>";
echo "<tr><td><label name=\"developerLabel\" id=\"developerLabel\" for=\"developer\">Tool developer:</label> </td><td>&#09; " .
     "<input name=\"developer\" id=\"developer\" type=\"checkbox\" " . ($developer ? "checked=\"checked\" " : " " ) . 
     ($user->isDeveloper() ? "" : "readonly=\"readonly\" disabled=\"disabled\"") . " />\n</td></tr>";
echo "<tr><td><label name=\"checkuserLabel\" id=\"checkuserLabel\" for=\"checkuser\">Checkuser:</label> </td><td>&#09;&#09; " .
     "<input name=\"checkuser\" id=\"checkuser\" type=\"checkbox\" " . ($checkuser ? "checked=\"checked\" " : " " ) . 
     ($user->isDeveloper() & $user->isCheckuser() ? "" : "readonly=\"readonly\" disabled=\"disabled\"") . " />\n</td></tr></table>";
echo "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Submit changes\" \> ";
echo "<input type=\"reset\" name=\"reset\" id=\"reset\" value=\"Reset\" onclick=\"setRequired(" . !$active . ")\" \>\n";
echo "</form>\n";
?>
		</td>
		<td style="width:60%;" valign="top">
			<h4>Logs for this user</h4>
			<?php echo printUserLogs($userId); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<?php

		echo "<form name=\"renameuser\" id=\"renameuser\" method=\"POST\" action=\"userMgmt.php?userId=" . $userId . "\">\n";
		echo "<label name=\"newNameLabel\" for=\"newName\" class=\"required\">Rename this user to:</label> <input type=\"text\" name=\"newName\" id=\"newName\" length=\"30\" value=\"" . (isset($_POST['newName']) ? $_POST['newName'] : "") . "\"/>\n";
		echo "<input type=\"submit\" name=\"rename\" id=\"rename\" value=\"Rename user\" \>\n";
		echo "</form>\n";
		
		?>
		</td>
	</tr>
		<td colspan="2">
			<h4>Assigned cases</h4>
			<?php echo printAssigned($userId); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<h4>Closed cases</h4>
			<?php echo printClosed($userId); ?>
		</td>
	</tr>
</table>

<?php 
	} // closes if(isset($_GET['userId']))
	else{
?>


<table style="background:none; border:none; width:100%;" cellspacing="0" cellpadding="0">
<tr>
<td style="width:50%" valign="top">
<h3>Unapproved accounts</h3>

<?php echo printUnapprovedAccounts(); ?>

<h3>Active accounts</h3>

<?php echo printActiveAccounts(); ?>

<h3>Developers</h3>

<?php echo printDevelopers(); ?>

</td>
<td style="width:50%" valign="top">
<h3>Tool administrators</h3>

<?php echo printAdmins(); ?>

<h3>Checkusers</h3>

<?php echo printCheckusers(); ?>

<h3>Inactive accounts</h3>

<?php echo printInactiveAccounts(); ?>

</td>
</tr>
</table>

<?php 
	} // ends the else block from if(isset($_GET['userId']))

} // ends the else block from if(!verifyAccess($GLOBALS['ADMIN']))

skinFooter();

?>