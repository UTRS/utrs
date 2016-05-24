<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/appealObject.php');
require_once('src/statsLib.php');
require_once('src/hooks.php');
require_once('template.php');
require_once('src/messages.php');

$lang = "en"; //for now
// make sure user is logged in, if not, kick them out
verifyLogin('home.php');

$secure = getCurrentUser()->getUseSecure();

$errorMessages = '';

//Template header()
skinHeader();

//Welcome message
echo '<p>'.SystemMessages::$tos['Welcome'][$lang].', ' . $_SESSION['user'] . '.</p>';

if (isset($_POST['acceptToS'])) {
	
	getCurrentUser()->setAcceptToS();
	
	echo SystemMessages::$tos['TOSAccept'][$lang];
	
} else {

echo SystemMessages::$tos['NewTerms'][$lang]."<br /><br />";
echo SystemMessages::$tos['ReviewToAccept'][$lang];
?>
<ul>
<li><a href="admin_privacy.php" target="_new"><?php echo SystemMessages::$tos['UTRSuserprivpol'][$lang];?></a>
<li><a href="https://wikitech.wikimedia.org/wiki/Wikitech:Labs_Terms_of_use" target="_new"><?php echo SystemMessages::$tos['WMFLabsToS'][$lang];?></a>
</ul>

<p><b><?php echo SystemMessages::$tos['LabsGeneralWarn'][$lang];?></b>
<?php echo SystemMessages::$tos['LabsDisclaimer'][$lang];?>

<?php if($lang != "en") {echo SystemMessages::$tos['Clarity'][$lang];}?>

<?php echo SystemMessages::$tos['ToSAgree'][$lang];?>
<form action="accepttos.php" method="post">
<input type="checkbox" name="acceptToS" /><?php echo SystemMessages::$tos['IAccept'][$lang];?>
<input type="submit" value="Submit" />
</form>

<?php
}
//Template footer()
skinFooter();

?>
