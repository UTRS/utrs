<?php
require_once('src/unblocklib.php');
require_once('src/noticeObject.php');

function skinHeader($script = '') {

$loggedIn = loggedIn();

$sitenoticeText = "";

if($loggedIn){
	try{
		$db = connectToDB();
		$query = $db->query("SELECT message FROM sitenotice ORDER BY messageID ASC");
		if($query === false){
			$error = var_export($db->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}

		while (($message = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
			$sitenoticeText .= "<li>" . Notice::format($message['message']) . "</li>";
		}

		$query->closeCursor();
	}
	catch(UTRSException $e){
		$sitenoticeText = "<li>An error occured when getting the sitenotice: " . $e->getMessage() . "</li>\n";
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
<link rel="stylesheet" href="unblock_styles.css?<?php /* Forces browsers to re-fetch the stylesheet when it changes */ echo sha1(file_get_contents('unblock_styles.css')) ?>">
<title>Unblock Ticket Request System - Register an Account</title>
<?php if($script){
	echo "<script type=\"text/javascript\">" . $script . "</script>";
}
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
</head>
<body>
<div id="header"><a <?php if($loggedIn) { ?>href="home.php"<?php }else{ ?>href="index.php"<?php } ?> >
English Wikipedia<br />
Unblock Ticket Request System <?php if(strpos(__FILE__, "/beta/") !== false){ echo "BETA"; } ?>
</a></div>
<?php if($sitenoticeText){?>
<div id="sitenotice">
	<ul>
		<?php echo $sitenoticeText; ?>
	</ul>
</div>
<?php }?>
<div id="subheader">
<ul id="navigation_menu">
<?php if ($loggedIn) { ?>
	<li id="home">
		<a href="<?php echo getRootURL() . 'home.php'; ?>">Home</a>
	</li>
	<li id="stats">
		<a href="<?php echo getRootURL() . 'statistics.php'; ?>">Statistics</a>
	</li>
	<li id="mgmtTemp">
		<a href="<?php echo getRootURL() . 'tempMgmt.php'; ?>">Manage/View Templates</a>
	</li>
	<?php if(verifyAccess($GLOBALS['ADMIN'])) { ?>
	<li id="mgmtUser">
		<a href="<?php echo getRootURL() . 'userMgmt.php'; ?>">User Management</a>
	</li>
	<li id="banMgmt">
		<a href="<?php echo getRootURL() . 'banMgmt.php'; ?>">Ban Management</a>
	</li>
	<li id="sitenoticeMgmt">
		<a href="<?php echo getRootURL() . 'sitenotice.php'; ?>">Sitenotice Management</a>
	</li>
	<?php } ?>
	<li id="search">
		<a href="<?php echo getRootURL() . 'search.php'; ?>">Search</a>
	</li>
	<li id="preferences">
		<a href="<?php echo getRootURL() . 'prefs.php'; ?>">Preferences</a>
	</li>
	<li id="privacyPolicy">
		<a href="<?php echo getRootURL() . 'privacy.php'; ?>">Privacy Policy</a>
	</li>
	<?php if(verifyAccess($GLOBALS['DEVELOPER'])) { ?>
	<li id="massEmail">
		<a href="<?php echo getRootURL() . 'massEmail.php'; ?>">Send Mass Email</a>
	</li>
	<?php } ?>
	<li id="logout">
		<a href="<?php echo getRootURL() . 'logout.php'; ?>">Logout</a>
	</li>
<?php } ELSE { ?>
	<li id="appealForm">
		<a href="<?php echo getRootURL() . 'index.php'; ?>">Appeal a Block</a>
	</li>
	<li id="GAB">
		<a href="http://en.wikipedia.org/wiki/Wikipedia:Guide_to_appealing_blocks">Guide to Appealing Blocks</a>
	</li>
	<li id="loginLink">
		<a href="<?php echo getRootURL() . 'login.php'; ?>">Admins: Log in to review requests</a>
	</li>
	<li id="register">
		<a href="<?php echo getRootURL() . 'register.php'; ?>">Admins: Request an account</a>
	</li>
	<li id="privacyPolicy">
		<a href="<?php echo getRootURL() . 'privacy.php'; ?>">Privacy Policy</a>
	</li>
<?php } ?>
</ul>
<div style="clear: both"></div>
</div>
<div id="main">
<?php
}

function skinFooter() {
?>
<br />

</div>
<div id="footer">
<?php if (loggedIn()) {?>
<p style="text-align:center; font-size:small;">Users active in the last five minutes: <?php echo getLoggedInUsers(); ?></p>
<?php }?>
<p>The Unblock Ticket Request System is a project hosted on the Wikimedia Toolserver intended to assist
users with the <a href="http://en.wikipedia.org/wiki/Wikipedia:Appealing_a_block" target="_NEW">unblock process</a> on the English Wikipedia. <br />
This project is licensed under the
<a id="GPL" href="http://www.gnu.org/copyleft/gpl.html" target="_NEW">GNU General Public License Version 3 or Later</a>.<br />
For questions or assistance with the Unblock Ticket Request System, please email our development team at
<a href="mailto:unblock@toolserver.org">unblock&#64;toolserver.org</a>.<br />
Version <?php echo getHeadCommit() ?>.</p>
</div>
</body>
</html>
<?php
}
?>
