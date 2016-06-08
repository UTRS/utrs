<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('template.php');
require_once('src/messages.php');
require_once('src/exceptions.php');

$destination = getRootURL() . 'home.php';

if(isset($_COOKIE["language"]) && !isset($_GET["reset"])){
	header("Location: " . $destination);
}
else {
	if(isset($_GET['set'])) {
		setcookie("language",$_GET['set'],time()+31557600);
	}
	else {
//Template header()
skinHeader();
try {
	throw new UTRSNetworkException('To use UTRS, it is required that you set a language and wiki to use. This is so that your appeal (or list of appeals for administrators) is selected from the right language. You can reset this at any time if you make a mistake. Once an appeal is filed in one language, it is impossible to change the language and wiki of that appeal.');
} catch (UTRSNetworkException $ex){
   	  $errorMessages = $ex->getMessage();
   	  displayError($errorMessages);
}

?>
<center>
<h2>Please select the wiki and language you are editing on</h2>
<img src="https://upload.wikimedia.org/wikipedia/en/thumb/a/ae/Flag_of_the_United_Kingdom.svg/40px-Flag_of_the_United_Kingdom.svg.png"> <a href = "langVerify.php?set=en">English Wikipedia (en.wikipedia.org)</a>
<br><br><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Flag_of_Portugal.svg/40px-Flag_of_Portugal.svg.png"> <a href = "langVerify.php?set=pt">Wikipédia portuguesa (pt.wikipedia.org)</a>
</center>
<?php 

skinFooter();
	}
}

?>