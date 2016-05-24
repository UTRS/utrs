<?php
//Created by the unblock-en-l dev team (test commit)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('src/unblocklib.php');
require_once('src/exceptions.php');
require_once('src/appealObject.php');
require_once('template.php');
require_once('src/messages.php');

// make sure user is logged in, if not, kick them out
verifyLogin('search.php');

$secure = getCurrentUser()->getUseSecure();

$errorMessages = '';

//Template header()
skinHeader();


?>
<form method="post">
	<div
		style="text-align: center; width: 100%; height: 100%; vertical-align: middle;">
		<input type="text" name="search_terms" style="width: 200px">&nbsp;<input
			type="submit" value="Search">
	</div>

</form>
<?php

if ($_POST || $_GET) {

	$db = connectToDB();

	if (isset($_GET['id'])) {

		if (!is_numeric($_GET['id'])) {
			throw UTRSIllegalArgumentException($_GET['id'], "a number", "search");
		}

		$query = $db->prepare("SELECT ip FROM appeal WHERE appealID = :appealID");

		$result = $query->execute(array(
			':appealID'	=> $_GET['id']));

		if(!$result){
			$error = var_export($query->errorInfo(), true);
			throw new UTRSDatabaseException($error);
		}

		$data = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();

		$ip_address = $data['ip'];
		$md5_ip_address = md5($ip_address);

		$query = $db->prepare("
			SELECT DISTINCT appealID, '0' as score
			FROM appeal
			WHERE ip = :ip
			   OR ip = :md5ip
			   OR MD5(ip) = :iptwo
			ORDER BY timestamp DESC;");

		$result = $query->execute(array(
			':ip'		=> $ip_address,
			':md5ip'	=> $md5_ip_address,
			':iptwo'	=> $ip_address));
	} else {
		//Search
		$search_terms = $_POST['search_terms'];

		$query = $db->prepare("
			SELECT DISTINCT
				a.appealID,
				MATCH (
					a.appealID,
					a.email,
					a.wikiAccountName,
					a.blockingAdmin,
					a.appealText,
					a.intendedEdits,
					a.otherInfo,
					c.comment
				) AGAINST(:searchterms IN BOOLEAN MODE) AS score
			FROM appeal AS a, comment AS c

			WHERE a.appealID = c.appealID

			HAVING score > 0.2
			ORDER BY score DESC");

		$result = $query->execute(array(
			':searchterms'		=> $search_terms));
	}

	if(!$result){
		$error = var_export($query->errorInfo(), true);
		throw new UTRSDatabaseException($error);
	}

	echo "<h2>Results</h2>";

	$found_any = false;
	while (($data = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
		$found_any = true;

		$appeal = Appeal::getAppealByID($data['appealID']);
		echo "<div class=\"search_header\"><a href=\"appeal.php?id=" . $appeal->getID() . "\">" . $appeal->getCommonName() . "</a> - Score: " . $data['score'] . "</div>";
		echo "<div class=\"search_body\"><i>" . $appeal->getAppeal() . "</i></div>";
	}
	$query->closeCursor();
	
	if (!$found_any) {
		echo "No results returned.";
	}
}

echo "<br><br>";

skinFooter();

?>
