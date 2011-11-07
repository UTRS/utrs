<?php
ini_set('error_reporting', -1);

class UTRSValidationException extends Exception{
	
	public static final $defaultMessage = "<b>There were errors processing your unblock appeal: </b>";
	
	public function __construct($errorMsg){
		parent::__construct($defaultMessage.concat($errorMsg), 10001, null);
	}
}

class UTRSIllegalModificationException extends Exception{
	
	public static final $defaultMessage = "<b>The action you requested could not be performed: </b>";
	
	public function __construct($errorMsg){
		parent::__construct($defaultMessage.concat($errorMsg), 10002, null);
	}
}

?>