<?php

class EmailTemplates {
	private $admin;
	private $appeal;

	public function __construct($admin, $appeal) {
		$this->admin = $admin;
		$this->appeal = $appeal;
	}

	private function template_replace_callback($pieces) {
		$args = explode('|', $pieces[1]);

		if (count($args) == 0) {
			return $pieces[0];
		}

		switch ($args[0]) {
		case 'adminname':
			return htmlspecialchars($this->admin->getUsername());

		case 'username':
			return htmlspecialchars($this->appeal->getCommonName());

		case 'enwp':
			if (count($args) < 2) {
				break;
			}

			$link = 'http://en.wikipedia.org/wiki/' . $args[1];
			$text = (count($args) < 3) ? $link : $args[2];

			return '<a href="' . htmlspecialchars($link) . '">' . htmlspecialchars($text) . '</a>';
		}

		return $pieces[0];
	}

	public function apply_to($text) {
		$text = preg_replace_callback('/{{([^}]+)}}/', array($this, 'template_replace_callback'), $text);
		$text = str_replace("\n", "<br />", $text);

		return $text;
	}

        public function censor_email($text) {
		return str_replace($this->appeal->getEmail(), censorEmail($this->appeal->getEmail()), $text);
        }
}

?>
