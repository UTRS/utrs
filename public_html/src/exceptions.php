<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('messages.php');

class UTRSException extends Exception{
	public function __construct($errorMsg, $code, $previous){
		parent::__construct($errorMsg, $code, $previous);
	}
}

class UTRSValidationException extends UTRSException{	
	public function __construct($errorMsg){
		$message = "<b>There were errors processing your unblock appeal: </b>" . $errorMsg;
		parent::__construct($message, 10001, null);
	}
}

class UTRSIllegalModificationException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>The action you requested could not be performed: </b>" . $errorMsg;
		parent::__construct($message, 10002, null);
	}
}

class UTRSDatabaseException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>A database error occured when attempting to process your request: </b><br />" . $errorMsg;
		parent::__construct($message, 10003, null);
	}
}

class UTRSCredentialsException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>Access denied: </b>" . $errorMsg;
		parent::__construct($message, 10004, null);
	}
}

class UTRSNetworkException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>An error occured while loading the page: </b>" . $errorMsg;
		parent::__construct($message, 10005, null);
	}
}

class UTRSIllegalArgumentException extends UTRSException{
	public function __construct($arg, $expected, $function){
		$message = "Argument " . $arg . " was provided to " . $function . " when " . $expected . " was expected.<br/>";
		$message .= "To UTRS users/appellants: This is likely not your fault, but an error on the part of a tool developer.<br/>";
		$message .= "Please try again later; if the problem persists, contact a tool developer with this message. Thanks!";
		parent::__construct($message, 10005, null);
	}
}

?>