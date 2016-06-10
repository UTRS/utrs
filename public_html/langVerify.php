<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('template.php');
require_once('src/messages.php');
require_once('src/exceptions.php');

$destination = getRootURL() . 'index.php';

if(isset($_COOKIE["language"]) && !isset($_GET["reset"])){
	header("Location: " . $destination);
}
else {
	if(isset($_GET['set'])) {
		setcookie("language",$_GET['set'],time()+31557600);
		header("Location: " . $destination);
	}
	else {
//Template header()
skinHeader();
if(!isset($_COOKIE["language"])) {
	try {
		throw new UTRSNetworkException(
				"<br>".SystemMessages::$error['LangError']['en']." ".
				"<br><br>".SystemMessages::$error['LangError']['pt']
				);
	} catch (UTRSNetworkException $ex){
	   	  $errorMessages = $ex->getMessage();
	   	  displayError($errorMessages);
	}
}

?>
<br><br><br><center>
<b><?php 
echo SystemMessages::$system['SelectLang']['en'];
echo "<br>";
echo SystemMessages::$system['SelectLang']['pt'];
?></b>
<br><br><br>
<img src="https://upload.wikimedia.org/wikipedia/en/thumb/a/ae/Flag_of_the_United_Kingdom.svg/40px-Flag_of_the_United_Kingdom.svg.png"> <a href = "langVerify.php?set=en<?php if (isset($_GET["reset"])) {echo "&reset=yes";} ?>">English Wikipedia (en.wikipedia.org)</a>
<br><br><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Flag_of_Portugal.svg/40px-Flag_of_Portugal.svg.png"> <a href = "langVerify.php?set=pt<?php if (isset($_GET["reset"])) {echo "&reset=yes";} ?>">Wikipédia portuguesa (pt.wikipedia.org)</a>
</center>
<?php 

skinFooter();
	}
}

?>