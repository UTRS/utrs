<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');
require_once('template.php');
require_once('src/messages.php');
require_once('src/exceptions.php');
if(loggedIn()){
	header("Location: " . getRootURL() . 'home.php');
	exit;
}

//Template header()
skinHeader();

?>
<br><br><br><center>
<b>Please select the Wiki you wish to login with:</b>
<br><br><br>
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Wikipedia_logo_%28svg%29.svg/20px-Wikipedia_logo_%28svg%29.svg.png"> <a href = "login.php?wiki=enwiki">English Wikipedia (en.wikipedia.org)</a>
<br><br><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/75/Wikimedia_Community_Logo.svg/20px-Wikimedia_Community_Logo.svg.png"> <a href = "login.php?wiki=meta">Meta Wiki (meta.wikimedia.org)</a>
</center>
<?php 
skinFooter();

?>