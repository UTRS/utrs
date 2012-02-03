<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('template.php');

// make sure user is logged in, if not, kick them out
verifyLogin('search.php');

$secure = getCurrentUser()->getUseSecure();

$errorMessages = '';

//Template header()
skinHeader();


?>
	<form method="post">
	<div style="text-align:center; width: 100%; height: 100%; vertical-align: middle;">
	<input type="text" name="search_terms" style="width: 200px">&nbsp;<input type="submit" value="Search">
	</div>
	
	</form>
<?php
	
if ($_POST) {
	
	$db = connectToDB();
	
	//Search
	$search_terms = mysql_real_escape_string($_POST['search_terms']);
	
	$query = "SELECT DISTINCT a.appealID, MATCH (a.email, a.wikiAccountName, a.blockingAdmin, a.appealText, a.intendedEdits, a.otherInfo, c.comment)";
	$query .= " AGAINST('" . $search_terms . "' IN BOOLEAN MODE) as score";
	$query .= " FROM appeal a, comment c";
	$query .= " WHERE a.appealID = c.appealID AND MATCH (a.email, a.wikiAccountName, a.blockingAdmin, a.appealText, a.intendedEdits, a.otherInfo, c.comment)";
	$query .= " AGAINST('" . $search_terms . "' IN BOOLEAN MODE)";
	$query .= " HAVING score > 0.2 ORDER BY score DESC;";
		
	$result = mysql_query($query, $db);
	
	if(!$result){
		$error = mysql_error($db);
		throw new UTRSDatabaseException($error);
	}
	
	$rows = mysql_num_rows($result);
	echo "<h2>Results</h2>";
	for ($i=0; $i < $rows; $i++) {
		$data = mysql_fetch_array($result);
		$appeal = Appeal::getAppealByID($data['appealID']);
		echo "<div class=\"search_header\"><a href=\"appeal.php?id=" . $appeal->getID() . "\">" . $appeal->getCommonName() . "</a> - Score: " . $data['score'] . "</div>";
		echo "<div class=\"search_body\"><i>" . $appeal->getAppeal() . "</i></div>";
	}
}


skinFooter();

?>
