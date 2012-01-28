<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('../src/exceptions.php');
require_once('../src/unblocklib.php');
require_once('template.php');

$errors = '';

verifyLogin('massEmail.php');

if(verifyAccess($GLOBALS['DEVELOPER']) & isset($_POST['submit'])){
	try{
		if(!isset($_POST['subject']) | strlen($_POST['subject']) == 0 |
		   !isset($_POST['emailBody']) | strlen($_POST['emailBody']) == 0){
			throw new UTRSIllegalModificationException("All fields are required.");   	
		}
		$subject = $_POST['subject'];
		$body = $_POST['emailBody'];
		$headers = "From: UTRS Development Team <unblock@toolserver.org>\r\n" . 
	        "Reply-to: UTRS Development Team <unblock@toolserver.org>\r\n";
		
		$db = connectToDB();
		$query = "SELECT email FROM user WHERE approved='1' AND active='1'";
		debug($query);
		$result = mysql_query($query, $db);
		if(!$result){
			$error = mysql_error($db);
			throw new UTRSDatabaseException($error);
		}
		$rows = mysql_num_rows($result);
		if($rows == 0){
			throw new UTRSDatabaseException("There are no users to send emails to? Check the database...");
		}
		$emails = '';
		for($i = 0; $i < $rows; $i++){
			$data = mysql_fetch_assoc($result);
			if($emails){
				$emails .= ', ';
			}
			$emails .= $data['email'];
		}
		$headers .= "Bcc: " . $emails;
		
		mail("", $subject, $body, $headers);
	}
	catch(UTRSException $e){
		$errors = $e->getMessage();
	}
}

skinHeader();

if(!verifyAccess($GLOBALS['DEVELOPER'])){
	displayError("<b>Access denied:</b> This function is only available to tool developers.");
}
else{
	echo "<h3>Developer mass email function</h3>\n";
	
	if($errors){
		displayError($errors);
	}
	else if(isset($_POST['submit'])){
		displaySuccess("Email sent successfully.");
	}
?>

<p>This function will send an email to all currently approved and active users of UTRS. This
should only be used to announce upcoming tool maintenance, downtime, or uptime, or otherwise
announce large-scale changes to the tool. Abuse of this may constitute violation of Toolserver
rules and thus may result in loss of your access as a developer to this project or your 
Toolserver account as a whole.</p>

<form name="sendEmail" id="sendEmail" method="POST" action="massEmail.php">
<label for="subject" id="subjectLabel" class="required">Subject: </label><input type="text" name="subject" id="subject" /><br />
<label for="emailBody" id="emailBodyLabel" class="required">Body:</label><br/>
<textarea name="emailBody" id="emailBody" rows="60" cols="15"></textarea><br />
<input type="submit" name="submit" id="submit" value="Send Email" /><br />
</form>

<p>Note: This email will be sent as a BCC to all approved and active UTRS users. It will
be sent from the unblock@toolserver.org email address. You must identify yourself in this
email as the one sending it. You are responsible for the content of this email.</p>

<?php 
}

skinFooter();
?>