<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/userObject.php');
require_once('../src/statsLib.php');
require_once('template.php');

verifyLogin('userMgmt.php');


skinHeader("function toggleRequired() {
	var label = document.getElementById('commentsLabel');
	if(label.className == 'required'){
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
		$numClosed = getNumberAppealsClosedByUser($userId);
		$wikiAccount = "User:" . $requestedUser->getWikiAccount();

		echo "<h3>" . $requestedUser->getUsername() . "</h3>";
		
		// processing goes here
?>

<table style="border:none; background: none;">
	<tr>
		<th style="text-align:left">User ID:</th>
		<td><?php echo $userId; ?></td>
	</tr>
	<tr>
		<th style="text-align:left">Wikipedia account:</th>
		<td><a href="<?php echo getWikiLink($wikiAccount, $secure); ?>"><?php echo $wikiAccount; ?></a></td>
	</tr>
	<tr>
		<th style="text-align:left">Number of closed appeals:</th>
		<td><?php echo $numClosed; ?></td>
	</tr>
	<tr>
		<th style="text-align:left">Registered:</th>
		<td><?php echo $registered; ?> UTC</td>
	</tr>
</table>

<h4>Access levels</h4>
<?php 
echo "<form name=\"accessControl\" id=\"accessControl\" method=\"POST\" action=\"userMgmt.php?userId=" . $userId . "\">\n";
// if not approved, require that the account be approved before any other changes are made
if(!$approved){
	echo "<label name=\"approvedLabel\" id=\"approvedLabel\" for=\"approved\" class=\"required\">Approve this account: " .
		 "</label> &#09; <input type=\"checkbox\" name=\"approved\" id=\"approved\" />\n";	
}
echo "<label name=\"activeLabel\" id=\"activeLabel\" for=\"active\">Activate account:</label> &#09; <input name=\"active\" " .
     "id=\"active\" type=\"checkbox\" onchange=\"toggleRequired()\" " . ($active ? "checked=\"true\"" : "" ) . " />\n";
echo "<label name=\"commentsLabel\" id=\"commentsLabel\" " . (!$active ? "class=\"required\"" : "") . " for=\"comments\" " .
	 " />Reason for deactivating this account:</label>\n";
echo "<textarea name=\"comments\" id=\"comments\" rows=\"3\" cols=\"30\" />" . $comments . "</textarea>\n";
echo "<label name=\"adminLabel\" id=\"adminLabel\" for=\"admin\">Tool administrator:</label> &#09; <input name=\"admin\" " .
	 "id=\"admin\" type=\"checkbox\" " . ($admin ? "checked=\"true\"" : "") . " />\n";
echo "<label name=\"developerLabel\" id=\"developerLabel\" for=\"developer\">Tool developer:</label> &#09; " .
     "<input name=\"active\" id=\"active\" type=\"checkbox\" " . ($developer ? "checked=\"true\" " : " " ) . 
     ($user->isDeveloper() ? "" : "readonly=\"true\"") . " />\n";
echo "<label name=\"checkuserLabel\" id=\"checkuserLabel\" for=\"checkuser\">Checkuser:</label> &#09;&#09; " .
     "<input name=\"checkuser\" id=\"checkuser\" type=\"checkbox\" " . ($checkuser ? "checked=\"true\" " : " " ) . 
     ($user->isDeveloper() & $user->isCheckuser() ? "" : "readonly=\"true\"") . " />\n";
echo "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Submit changes\" \> ";
echo "<input type=\"reset\" name=\"reset\" id=\"reset\" value=\"Reset\" \>\n";
echo "</form>\n";


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