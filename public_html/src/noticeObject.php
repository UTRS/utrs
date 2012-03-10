<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('exceptions.php');
require_once('unblocklib.php');
require_once('userObject.php');

class Notice{
	private $messageId;
	private $message;
	private $author;
	private $lastEditTime;
	
	public function __construct(array $vars, $fromDB){
		if($fromDB){
			$this->messageId = $vars['messageID'];
			$this->message = $vars['message'];
			$this->author = User::getUserById($vars['author']);
			$this->lastEditTime = $vars['time'];
		}
		else{
			$mess = sanitizeText($vars['message']);

			validate($mess);
			
			$this->message = sanitizeText($mess);
			$this->author = getCurrentUser();
			
			$this->insert();
		}
	}
	
	private function validate($message){
		if(strlen($message) > 2048){
			throw new UTRSIllegalModificationException("Your message is too long to store in the database. " .
				"Please shorten your message to less than 2048 characters. (Current length: " . strlen($mess) . ")");
		}
		
		$syntaxError = false;
		
		// search through each formatting type and ensure that none overlap
		// *this /is not* ok/ - it'll break the page
		// *this /is/ ok* - that'll display correctly
		
		if(strpos($message, "*") !== false){
			$start = strpos($message, "*");
			$end = strpos($message, "*", $start + 1);
			if($end){
				$substring = substr($message, $start, $end - $start);
			}
			else{
				// unmatched is ok, will be ignored by regex
			}
		}
	}
	
	private function insert(){
				
		$db = connectToDB();
		
		$query = "INSERT INTO sitenotice (message, author) VALUES ('" . 
				mysql_escape_string($this->message) . "', '" . $this->author->getUserId() . "')";
		
		debug($query);
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->messageId = mysql_insert_id($db);
		
		$query = "SELECT time FROM sitenotice WHERE messageID='" . $this->messageId . "'";
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$data = mysql_fetch_assoc($result);
		
		$this->lastEditTime = $data['time'];
	}
	
	public function update($message){
		$message = sanitizeText($message);
		
		$db = connectToDB();
		
		$query = "UPDATE sitenotice SET message = '" . mysql_escape_string($message) . 
			"', author = '" . getCurrentUser()->getUserId() . "' WHERE messageID = '" . 
			$this->messageId . "'";
		
		debug($query);
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->message = $message;
		$this->author = getCurrentUser();
		
		$query = "SELECT time FROM sitenotice WHERE messageID='" . $this->messageId . "'";
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$data = mysql_fetch_assoc($result);
		
		$this->lastEditTime = $data['time'];
	}
	
	public static function delete($messageId){
		$query = "DELETE FROM sitenotice WHERE messageID='" . $messageId . "'";
		
		debug($query);
		
		$db = connectToDB();
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
	}
	
	public function getMessageId(){
		return $this->messageId;
	}
	
	public function getMessage(){
		return $this->message;
	}
	
	public function getAuthor(){
		return $this->author;
	}
	
	public function getLastEditTime(){
		return $this->lastEditTime;
	}
	
	public function getFormattedMessage(){
		return $this->format($this->message);
	}
	
	public static function format($string){
		$string = sanitizeText($string);
		
		echo $string . "<br/>\n";
		echo preg_match("/.*\[(red|green|blue|yellow|orange|purple|gray|grey|#[0-9a-fA-F]{6,6}|#[0-9a-fA-F]{3,3})\].*?\[\/\1\].*/", $string)  . "<br/>\n";;
		
		// while we have matching color tokens...
		while(preg_match("/.*\[(red|green|blue|yellow|orange|purple|gray|grey|#[0-9a-fA-F]{6,6}|#[0-9a-fA-F]{3,3})\].*?\[\/\1\].*/", $string)){
			// handle [red]color[/red]
			// supported tags: red, orange, yellow, green, blue, purple, grey, gray, three- or six-digit hex code
			$string = preg_replace(
			'/\[(red|green|blue|yellow|orange|purple|gray|grey|#[0-9a-fA-F]{6,6}|#[0-9a-fA-F]{3,3})\](.+?)\[\/\1\]/',
			'<span style="color:$1">$2</span>', 
			$string);
			
			echo $string  . "<br/>\n";;
		echo preg_match("/.*\[(red|green|blue|yellow|orange|purple|gray|grey|#[0-9a-fA-F]{6,6}|#[0-9a-fA-F]{3,3})\].*?\[\/\1\].*/", $string)  . "<br/>\n";;
		
		}
		// handle /italics/
		$string = preg_replace('/([^<])\/(.+?)([^<])\//', '$1<i>$2$3</i>', $string);
		// handle *bolds*
		$string = preg_replace('/\*(.+?)\*/', '<b>$1</b>', $string);
		// handle _underlines_
		$string = preg_replace('/_(.+?)_/', '<u>$1</u>', $string);
		// handle {http://enwp.org links}
		$string = preg_replace('/\{http(\S+?) (.+?)\}/', '<a href="http$1">$2</a>', $string);
			
		return $string;
	}
	
	public static function getNoticeById($messageId){
		$query = "SELECT * FROM sitenotice WHERE messageId = '" . $messageId . "'";
		
		debug($query);
		
		$db = connectToDB();
		
		$result = mysql_query($query, $db);
		
		if(!$result){
			$error = mysql_error($db);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		return new Notice(mysql_fetch_assoc($result), true);
	}
}

?>