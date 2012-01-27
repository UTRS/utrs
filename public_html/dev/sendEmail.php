<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

echo "test5";

require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');
require_once('../src/userObject.php');
require_once('../src/templateObj.php');
require_once('../src/appealObject.php');
require_once('template.php');

$errors = '';

if(!isset($_GET['id'])){
	// don't really know how to handle this, so...
	header("Location: " . getRootURL() . "home.php");
	// ...off you go
}

$id = $_GET['id'];

verifyLogin("appeal.php?id=" . $id);

skinHeader();

echo "test4";
skinFooter();

?>