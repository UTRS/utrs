<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/appealObject.php');
require_once('../src/userObject.php');
require_once('../src/templateObj.php');
require_once('../src/logObject.php');
require_once('template.php');

$errors = '';

if(!isset($_GET['id'])){
	// if not supposed to be here, send to appeals page
	header("Location: " . getRootURL() . "index.php");
}
try{
	$id = $_GET['id'];
	$appeal = Appeal::getAppealByID($id);
	if(strcmp($appeal->getStatus(), Appeal::$STATUS_CLOSED) === 0){
		throw new UTRSIllegalModificationException("Your appeal has been marked as closed, which means the adminstrator" .
		   " reviewing your appeal feels the matter is resolved. If you received a message that indicates you will be " .
		   "unblocked, but you still cannot edit, please try again in a few minutes. If you are still unable to edit," .
		   " you may wish to post <tt>{{unblock|<contents of your email here>}} to your " .
		   "<a href=\"http://enwp.org/Special:Mytalk\">User Talk: page</a>. If you appeal was declined, then you may " .
		   "wish to appeal again in several month's time, or appeal to the Ban Appeals Subcommittee by emailing " .
		   "arbcom-l AT lists DOT wikimedia DOT org.");
		   
	}
	if(!isset($_GET['confirmEmail']) | strcmp($_GET['confirmEmail'], $appeal->getEmail()) !== 0){
		throw new UTRSIllegalModificationException("Please use the link provided to you in your email to access this page. " .
		   "This security step assures us that we are still talking to the same person. Thank you.");
	}

	if(isset($_POST['submit'])){
		// stuff goes here
		$log = Log::getCommentsByAppealId($appeal->getID());
		
		// more stuff
	}
		
}
catch(UTRSException $e){
	$errors = $e->getMessage();
}
