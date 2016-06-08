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
function cookieExist() {
	if (!isset($_COOKIE['language'])) {
		return false;
	}
	else {
		return true;
	}
}
function getCookie() {
	return $_COOKIE['language'];
}
?>