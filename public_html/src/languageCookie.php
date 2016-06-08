<?php
require_once('src/unblocklib.php');

function checkCookie() {
	if (!isset($_COOKIE['language'])) {
		$destination = getRootURL() . 'langVerify.php';
		return header("Location: " . $destination);
	}
	else {
		return "";
	}
}
?>