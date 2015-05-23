<?php

class SystemMessages {
	public static $user = array(
		"AppealNotNumeric" => array(
				"en" => "The appeal ID is not numeric",
				"pt" => ""
		)
		
	);

	public static $error = array(
		"AppealNotNumeric" => array(
				"en" => "The appeal ID is not numeric",
				"pt" => ""
		)
	
	);
	public static function getUserMessage($type, $name, $lang = "en") {
		return $this->error[$name][$lang];
	}
}