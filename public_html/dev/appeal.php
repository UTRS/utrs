<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('../src/userObject.php');
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

?>

<h1>Details for Request #<?php echo $appeal->getID(); ?>:</h1><br>
<br>
| <a href="<?php echo getWikiLink($appeal->getUserPage()); ?>" target="_new"><?php echo $appeal->getCommonName(); ?></a> | <a href="?id=<?php echo $_GET['id']; ?>&action=reserve&user=<?php echo $user->getUserId(); ?>">Mark as being handled</a><br>
<br>
<br>
Account links: <a href="<?php echo getWikiLink($appeal->getUserPage()); ?>" target="_new">User Page</a> | <a href="<?php echo getWikiLink("Special:Block/" . $appeal->getCommonName()); ?>" target="_new">Block Log</a> | <a href="<?php echo getWikiLink("Special:Contributions/" . $appeal->getCommonName()); ?>" target="_new">Contribs</a>









