<?php
error_reporting(E_ALL);

class UTRSException extends Exception{
	public function __construct($errorMsg, $code, $previous){
		parent::__construct($errorMsg, $code, $previous);
	}
}

class UTRSValidationException extends UTRSException{	
	public function __construct($errorMsg){
		$message = "<b>There were errors processing your unblock appeal: </b>" . $errorMsg;
		parent::__construct($message, 10001);
	}
}

class UTRSIllegalModificationException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>The action you requested could not be performed: </b>" . $errorMsg;
		parent::__construct($message, 10002);
	}
}

class UTRSDatabaseException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>A database error occured when attempting to process your request: </b><br />" . $errorMsg;
		parent::__construct($message, 10003);
	}
}

?>