<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/languageCookie.php');
echo checkCookie();
require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/appealObject.php');
require_once('src/statsLib.php');
require_once('src/hooks.php');
require_once('template.php');
require_once('src/messages.php');

// make sure user is logged in, if not, kick them out
verifyLogin('home.php');

$secure = getCurrentUser()->getUseSecure();

$errorMessages = '';

//Template header()
skinHeader();

//Welcome message
echo '<p>Welcome, ' . $_SESSION['user'] . '.</p>';

//Get user's personalized hooks
init();
getHooks();

echo "<div style=\"clear: both\"></div>";

//Template footer()
skinFooter();

?>
