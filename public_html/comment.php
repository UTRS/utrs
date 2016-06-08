<?php
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

?>
<form action="appeal.php?id=<?php echo $_GET['id']; ?>&action=comment" method="POST">
<table>
	<tr>
		<th align=left style="width:70px">Appeal:</th>
		<td><?php echo $appeal->getCommonName(); ?></td>
	</tr>
	<tr>
		<td colspan="2" align=left><textarea name="comment" rows="15" cols="60"></textarea></td>
	</tr>
	<tr>
		<td colspan="2" align=left><input type="submit" value="Submit comment"></td>
	</tr>
</table>
</form>
<?php 

skinFooter();
?>