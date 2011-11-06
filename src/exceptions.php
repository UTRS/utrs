<?php

class UTRSValidationException{
	
	private $message;
	
	public final $defaultMessage = "<b>There were errors processing your unblock appeal: </b>";
	
	public function __construct($errorMsg){
		$this->message = $defaultMessage.concat($errorMsg);
	}
	
	public function getMessage(){
		return $this->message;
	}
}

class UTRSIllegalModificationException{
	
	private $message;
	
	public final $defaultMessage = "<b>The action you requested could not be performed: </b>";
	
	public function __construct($errorMsg){
		$this->message = $defaultMessage.concat($errorMsg);
	}
	
	public function getMessage(){
		return $this->message;
	}
}

?>