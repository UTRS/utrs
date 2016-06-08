<?php
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
		<td colspan="2" align=left>
			<textarea name="comment" id="comment" rows="15" cols="60" onblur="sizeAudit('comment','sizeComment',10000)"></textarea>
			<p id="sizeComment"></p>
		</td>
	</tr>
	<tr>
		<td colspan="2" align=left><input type="submit" id="submit" value="Submit comment"></td>
	</tr>
</table>
</form>
<script type="text/javascript">
function sizeAudit(item,name,max) {
	var size = document.getElementById(item).value.length;
	if(size>max){
		document.getElementById(item).style.border = "thin solid #FF0000";
		document.getElementById(name).innerHTML = "You have inputed too much content into the above text box. Please reduce to "+max+" charecters.";
		document.getElementById(name).style.color = "#FF0000";
		document.getElementById(name).style.background = "#FFFFFF";
		document.getElementById("submit").disabled = true;
	}
	else {
		document.getElementById(item).style.border = "none none #FF0000";
		document.getElementById(name).innerHTML = "";
		document.getElementById(name).style.color = "#FFFFFF";
		document.getElementById(name).style.background = "none";
		document.getElementById("submit").disabled = false;
	}
}
</script>
<?php 

skinFooter();
?>