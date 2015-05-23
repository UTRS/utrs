<?php

class SystemMessages {
	static $user = array(
		"AppealNotNumeric" => array(
				"en" => "The appeal ID is not numeric",
				"pt" => ""
		)
		
	);

	static $error = array(
		"AppealNotNumeric" => array(
				"en" => "The appeal ID is not numeric",
				"pt" => ""
		)
	
	);
	public static function getUserMessage($type, $name, $lang = "en") {
		return $this->error[$name][$lang];
	}
}