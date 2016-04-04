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


// make sure user is logged in, if not, kick them out
verifyLogin('home.php');

$secure = getCurrentUser()->getUseSecure();

$errorMessages = '';

//Template header()
skinHeader();

//Welcome message
echo '<p>Welcome, ' . $_SESSION['user'] . '.</p>';

if (isset($_POST['acceptToS'])) {
	
	getCurrentUser()->setAcceptToS();
	
	echo "Thank you, your account has been updated.  Click <a href=\"home.php\">here</a> to go to the homepage.";
	
} else {
?>
With the development of UTRS, this project occasionally requires a modified terms of service than when you initially registered.  To continue to participate in this
system, for which your time is greatly appreciated, we require you to first accept these new terms.<br />
<br />
Please review the following policies and click "I accept" below to continue:
<ul>
<li><a href="admin_privacy.php" target="_new">UTRS Member Privacy Policy and Duties</a>
<li><a href="https://wikitech.wikimedia.org/wiki/Wikitech:Labs_Terms_of_use" target="_new">Wikimedia Labs terms of service</a>
</ul>

<p><b>Warning: Do not use the Labs Project (this site) if you do not agree to the following: information shared with the Labs Project, including usernames and passwords, will be made available to volunteer administrators and may not be treated confidentially.</b>
<p>Volunteers may have full access to the systems hosting the projects, allowing them access to any data or other information you submit.
<p>As a result, use of your real Wikimedia credentials is highly discouraged in wmflabs.org projects. You should use a different password for your account than you would on projects like Wikipedia, Commons, etc.
<p>By creating an account in this project and/or using other Wikimedia Labs Services, you agree that the volunteer administrators of this project will have access to any data you submit.
<p>Since access to this information by volunteers is fundamental to the operation of Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.

If you agree check here and click submit:
<form action="accepttos.php" method="post">
<input type="checkbox" name="acceptToS" />I Accept
<input type="submit" value="Submit" />
</form>

<?php
}
//Template footer()
skinFooter();

?>
