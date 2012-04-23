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
	const colorCodes = "(red|green|blue|yellow|orange|purple|gray|grey|#[0-9a-f]{3,3}|#[0-9a-f]{6,6})";
	
	public function __construct(array $vars, $fromDB){
		if($fromDB){
			$this->messageId = $vars['messageID'];
			$this->message = $vars['message'];
			$this->author = User::getUserById($vars['author']);
			$this->lastEditTime = $vars['time'];
		}
		else{
			$mess = sanitizeText($vars['message']);

			$this->validate($mess);
			
			$this->message = sanitizeText($mess);
			$this->author = getCurrentUser();
			
			$this->insert();
		}
	}
	
	private static function validate($message){
		if(strlen($message) > 2048){
			throw new UTRSIllegalModificationException("Your message is too long to store in the database. " .
				"Please shorten your message to less than 2048 characters. (Current length: " . strlen($mess) . ")");
		}
		
		$syntaxCodes = array();
		$syntaxIndex = 0;
		
		// scan through the string and be sure no formatting overlaps
		// *this /is not* ok/ - it'll break the page
		// *this /is/ ok* - that'll display correctly
		for($i = 0; $i < strlen($message); $i++){
			$char = substr($message, $i, 1); // get each character
			
			if($char == '*' || $char == '/' || $char == '_'){
				if($syntaxIndex != 0 && $syntaxCodes[$syntaxIndex - 1] == $char){
					// if the last syntax token encountered matches
					// remove it from the stack
					unset($syntaxCodes[$syntaxIndex - 1]);
					$syntaxIndex--;
				}
				else{
					// else, see if it exists in the stack
					Notice::checkForExistingToken($syntaxCodes, $char);
					// if not, add it to the stack
					$syntaxCodes[$syntaxIndex] = $char;
					$syntaxIndex++;
				}
			}
			else if($char == '}'){
				if($syntaxIndex != 0 && $syntaxCodes[$syntaxIndex - 1] == '{'){
					// if the last syntax token encountered starts
					// a link, remove it
					unset($syntaxCodes[$syntaxIndex - 1]);
					$syntaxIndex--;
				}
				else{
					// else, see if it exists in the stack
					Notice::checkForExistingToken($syntaxCodes, '{');
					// if not, add it to the stack
					$syntaxCodes[$syntaxIndex] = $char;
					$syntaxIndex++;
				}
			}
			else if($char == '{'){
				if(substr($message, $i + 1, 4) == "http"){
					//make sure we aren't already in a link
					Notice::checkForExistingToken($syntaxCodes, '{');
					// advance loop to next space to avoid issues with
					// italics and / signs in the url
					$i = strpos($message, ' ', $i);
					// add link to the stack
					$syntaxCodes[$syntaxIndex] = $char;
					$syntaxIndex++;
				}
				// if next four characters aren't http, ignore
			}
			else if($char == '['){
				$end = strpos($message, ']', $i);
				if($end !== false){
					if(substr($message, $i + 1, 1) != '/'){
						// if opening a color tag
						$color = substr($message, $i + 1, ($end - 1) - $i);
						// make sure it's a valid color
						if($color !== false && preg_match('~^' . Notice::colorCodes . '$~i', $color)){
							// add to stack
							$syntaxCodes[$syntaxIndex] = '[/' . $color . ']';
							$syntaxIndex++;
							// advance loop to save time
							$i = $end; 
						}
					}
					else{
						// if closing a color tag
						$color = substr($message, $i + 2, ($end - 2) - $i);
						// make sure it's a valid color
						if($color !== false && preg_match('~^' . Notice::colorCodes . '$~i', $color)){
							// if on top of stack, remove
							if($syntaxIndex != 0 && $syntaxCodes[$syntaxIndex - 1] == '[/' . $color . ']'){
								unset($syntaxCodes[$syntaxIndex]);
								$syntaxIndex--;
								// advance loop to save time
								$i = $end; 
							}
							else{
								Notice::checkForExistingToken($syntaxCodes, '[/' . $color . ']');
							}
						}
					}
				}
			}
		}
		// if we get down here with no exceptions, it's good to go.
	}
	
	private function checkForExistingToken($syntaxCodes, $match){
		$syntaxError = "Your message contains overlapping formatting which will not display properly. Special" .
			" formats within special format must end before the preceding format ends." .
			" For example, '<tt>this /string *is* formatted/ correctly</tt>', however " .
			"'<tt>this *string /is* not/ correct</tt>' because the bold section ends before the italic section does." .
			" Furthermore, links may not contain other links.";

		for($j = 0; $j < sizeOf($syntaxCodes); $j++){
			if($syntaxCodes[$j] == $match){
				throw new UTRSIllegalModificationException($syntaxError);
			}
		}
	}

	private function updateTime() {
		$db = connectToDB();

		$query = $db->prepare("SELECT time FROM sitenotice WHERE messageID = :messageId");
		$result = $query->execute(array(
			':messageId'	=> $this->messageId));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$data = $query->fetch(PDO::FETCH_ASSOC);
		if ($data === false) {
			throw new UTRSDatabaseException('Sitenotice was added but could not be found.');
		}
		
		$this->lastEditTime = $data['time'];

		$query->closeCursor();
	}
	
	private function insert(){
		$db = connectToDB();

		$query = $db->prepare("INSERT INTO sitenotice (message, author) VALUES (:message, :author)");
		$result = $query->execute(array(
			':message'	=> $this->message,
			':author'	=> $this->author->getUserId()));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->messageId = $db->lastInsertId();

		$this->updateTime();
	}
	
	public function update($message){
		$message = sanitizeText($message);
		
		$db = connectToDB();
		
		$query = $db->prepare(
			"UPDATE sitenotice SET message = :message, author = :author " .
			"WHERE messageID = :messageId");

		$result = $query->execute(array(
			':message'	=> $message,
			':author'	=> getCurrentUser()->getUserId(),
			':messageId'	=> $this->messageId));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$this->message = $message;
		$this->author = getCurrentUser();

		$this->updateTime();
	}
	
	public static function delete($messageId){
		$db = connectToDB();

		$query = $db->prepare("DELETE FROM sitenotice WHERE messageID = :messageId");
		$result = $query->execute(array(
			':messageId'	=> $messageId));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
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
		Notice::validate($string);
		
		// handle /italics/
		// have to handle like this instead of regex
		// because slashes appear too much
		$open = false;
		$length = strlen($string);
		for($i = 0; $i < $length; $i++){
			$char = substr($string, $i, 1);
			$substr = substr($string, $i, 4);
			if($substr == "http"){
				// if start of link, jump ahead to next space
				$space = strpos($string, ' ', $i);
				if($space === false){
					$i = $length;
				}
				else{
					$i = $space;
				}
			}
			else if($char == '[' && substr($string, $i + 1, 1) == '/'){
				$end = strpos($string, ']', $i);
				$color = substr($string, $i + 2, ($end - 2) - $i);
				// make sure it's a valid color
				if($color !== false && preg_match('~^' . Notice::colorCodes . '$~i', $color)){
					// skip over it
					$i = $i + 3 + strlen($color);
				}
			}
			else if($char == '/'){
				if($open === false){
					// don't replace just yet, may be mismatched
					$open = $i;
				}
				// 012/456/89
				else{
					$partOne = substr($string, 0, $open);
					$partTwo = substr($string, $open + 1, $i - ($open + 1));
					$partThree = substr($string, $i + 1);
					$string = $partOne . '<i>' . $partTwo . '</i>' . $partThree;
					$length = strlen($string);
					$i = $i + 7; // adds length of both tags
					$open = false;
				}
			}
		}
		// while we have matching color tokens...
		while(preg_match('~^.*?\[' . Notice::colorCodes . '\].+?\[/\1\].*?$~i', $string)){
			// handle [red]color[/red]
			// supported tags: red, orange, yellow, green, blue, purple, grey, gray, three- or six-digit hex code
			$string = preg_replace(
			'~\[' . Notice::colorCodes . '\](.+?)\[/\1\]~i',
			'<span style="color:$1">$2</span>', 
			$string);
		}
		// handle {http://enwp.org links}
		$string = preg_replace('/\{http(\S+?) (.+?)\}/', '<a href="http$1">$2</a>', $string);
		// handle *bolds*
		$string = preg_replace('/\*(.+?)\*/', '<b>$1</b>', $string);
		// handle _underlines_
		$string = preg_replace('/_(.+?)_/', '<u>$1</u>', $string);
			
		return $string;
	}
	
	public static function getNoticeById($messageId){
		$db = connectToDB();

		$query = $db->prepare("SELECT * FROM sitenotice WHERE messageId = :messageId");
		$result = $query->execute(array(
			':messageId'	=> $messageId));
		
		if(!$result){
			$error = var_export($query->errorInfo(), true);
			debug('ERROR: ' . $error . '<br/>');
			throw new UTRSDatabaseException($error);
		}
		
		$row = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();

		if ($row === false) {
			throw new UTRSDatabaseException("No sitenotice with ID $messageId.");
		}

		return new Notice($row, true);
	}
}

?>
