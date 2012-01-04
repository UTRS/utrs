<?php

session_id('UTRSLogin');
session_name('UTRSLogin');
session_start();
$_SESSION['username'] = null;
$_SESSION['password'] = null;

// destroy the cookie
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000,
	$params["path"], $params["domain"],
	$params["secure"], $params["httponly"]
);

session_destroy();

header('Location: ' . getRootURL() . 'login.php?logout=true');
exit;

?>