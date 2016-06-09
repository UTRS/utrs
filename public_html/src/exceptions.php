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
		$message = "<b>".['']." </b>" . $errorMsg;
		parent::__construct($message, 10001, null);
	}
}

class UTRSIllegalModificationException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>".['']." </b>" . $errorMsg;
		parent::__construct($message, 10002, null);
	}
}

class UTRSDatabaseException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>".['']." </b><br />" . $errorMsg;
		parent::__construct($message, 10003, null);
	}
}

class UTRSCredentialsException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>".['']." </b>";
		parent::__construct($message, 10004, null);
	}
}

class UTRSNetworkException extends UTRSException{
	public function __construct($errorMsg){
		$message = "<b>".['']." </b>"  . $errorMsg;
		parent::__construct($message, 10005, null);
	}
}

class UTRSIllegalArgumentException extends UTRSException{
	public function __construct($arg, $expected, $function){
		$message = ['']. " " . $arg .   " ".['']." ".$function .  " ".['']." ". $expected ." ".['']."<br />"; 
		$message .= "['']<br/>";
		$message .= SystemMessages::$error['TryAgainLater'][$lang];
		parent::__construct($message, 10005, null);
	}
}

?>