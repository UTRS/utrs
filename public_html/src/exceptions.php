<?php
ini_set('error_reporting', -1);

class UTRSValidationException extends Exception{	
	public function __construct($errorMsg){
		$message = "<b>There were errors processing your unblock appeal: </b>" . $errorMsg;
		parent::__construct($message, 10001, null);
	}
}

class UTRSIllegalModificationException extends Exception{
	public function __construct($errorMsg){
		$message = "<b>The action you requested could not be performed: </b>" . $errorMsg;
		parent::__construct($message, 10002, null);
	}
}

class UTRSDatabaseException extends Exception{
	public function __construct($errorMsg){
		$message = "<b>A database error occured when attempting to process your request: </b><br />" . $errorMsg;
		parent::__construct($message, 10003, null);
	}
}

?>