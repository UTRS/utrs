<?php
ini_set('error_reporting', -1);

class UTRSValidationException extends Exception{	
	public function __construct($errorMsg){
		echo 'point 1';
		$message = "<b>There were errors processing your unblock appeal: </b>" . $errorMsg;
		echo $message;
		parent::__construct($message, 10001, null);
		echo 'point 1.5';
	}
}

class UTRSIllegalModificationException extends Exception{
	public function __construct($errorMsg){
		echo 'point 2';
		$message = "<b>The action you requested could not be performed: </b>" . $errorMsg;
		echo $message;
		parent::__construct($message, 10002, null);
		echo 'point 2.5';
	}
}

?>