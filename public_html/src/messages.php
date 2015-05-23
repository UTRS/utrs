<?php

class SystemMessages {
	public $user = array(
		"AppealNotNumeric" => array(
				"en" => "The appeal ID is not numeric",
				"pt" => ""
		)
		
	);

	public $error = array(
		"AppealNotNumeric" => array(
				"en" => "The appeal ID is not numeric",
				"pt" => ""
		)
	
	);
	public function getUserMessage($type, $name, $lang = "en") {
		return $this->error[$name][$lang];
	}
}