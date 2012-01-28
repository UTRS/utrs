<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('../src/unblocklib.php');
require_once('../src/exceptions.php');
require_once('../src/userObject.php');
require_once('../src/templateObj.php');
require_once('../src/statsLib.php');
require_once('template.php');

verifyLogin('tempMgmt.php');

$errors = '';

try{
	if(verifyAccess($GLOBALS['ADMIN'])){
		if(isset($_POST['submit'])){

			// validate first
			if(!isset($_POST['name']) | !isset($_POST['text'])){
				throw new UTRSIllegalModificationException("All fields are required.");
			}

			$name = $_POST['name'];
			$text = $_POST['text'];
			
			if (isset($_POST['statusUser'])) {
				$statusUser = 1;
			} else {
				$statusUser = 0;
			}
			if (isset($_POST['statusClose'])) {
				$statusClose= 1;
			} else {
				$statusClose = 0;
			}

			if(strlen($name) == 0 | strlen($text) == 0){
				throw new UTRSIllegalModificationException("All fields are required.");
			}
				
			if(strlen($name) > 40){
				throw new UTRSIllegalModificationException("The name of a template must be less than 40 characters" .
				   " long. The name you have entered is " . strlen($name) . " characters in length.");
			}
			if(strlen($text) > 4096){
				throw new UTRSIllegalModificationException("The text of a template must be less than 2048 characters" .
				   " long. The text you have entered is " . strlen($text) . " characters in length.");
			}

			// now actually process the request
			if(strcmp($_GET['id'],'new') == 0){
				$template = new Template($_POST, false);
					
				if($template != null){
					// load the "view/edit" screen
					header("Location: " . getRootURL() . 'tempMgmt.php?id=' . $template->getId());
				}
				else{
					throw new UTRSException("An unexpected error has occured. Please contact a tool developer.", 10000, null);
				}
			}
			else{
				$template = Template::getTemplateById($_GET['id']);

				if(strcmp($name, $template->getName()) == 0 & strcmp($text, $template->getText()) == 0){
					unset($_POST); // act as though nothing happened
				}
				if(strcmp($name, $template->getName()) != 0){
					$template->setName($name);
				}
				if(strcmp($text, $template->getText()) != 0){
					$template->setText($text);
				}
				$template->setStatus($statusUser, $statusClose);
				
			}
		}
		else if(isset($_POST['delete'])){
			$id = $_GET['id'];
			
			$template = Template::getTemplateById($id);

			$template->delete();

			$template = null;

			header("Location: " . getRootURL() . "tempMgmt.php?deleted=" . $id);
		}
	}
}
catch(UTRSException $e){
	$errors = $e->getMessage();
}

skinHeader();

echo "<h2>Template Management</h2>\n";

// default list screen
if(!isset($_GET['id'])){
	
	if(verifyAccess($GLOBALS['ADMIN'])){
		if(isset($_GET['deleted'])){
			displaySuccess("Successfully deleted Template #" . $_GET['deleted']);
		}
?>
<p><a href="tempMgmt.php?id=new">Create a new template</a></p>

<?php }

	echo printTemplateList();

} // closes if(!isset($_GET['id'])){
else if(strcmp($_GET['id'], 'new') == 0){
	if(!verifyAccess($GLOBALS['ADMIN'])){
		displayError("<b>Access denied:</b> Only tool administrators may create new templates.");
	}
	else{
		echo "<h3>New template</h3>";
		
		if($errors){
			displayError($errors);
		}
		
		$name = (isset($_POST['name']) ? $_POST['name'] : null);
		$text = (isset($_POST['text']) ? $_POST['text'] : null);
		$text = (isset($_POST['statusUser']) ? $_POST['statusUser'] : null);
		$text = (isset($_POST['statusClose']) ? $_POST['statusClose'] : null);
		
		echo "<form name=\"createTemplate\" id=\"createTemplate\" method=\"POST\" action=\"tempMgmt.php?id=new\">\n";
		echo "<label name=\"nameLabel\" id=\"nameLabel\" for=\"name\" class=\"required\">Name:</label> ";
		echo "<input name=\"name\" id=\"name\" type=\"text\" length=\"40\" value=\"" . $name . "\" />\n";
		echo "<label name=\"textLabel\" id=\"textLabel\" for=\"text\" class=\"required\">Text:</label>\n";
		echo "<textarea name=\"text\" id=\"text\" rows=\"12\" cols=\"60\">" . $text . "</textarea>\n";
		echo "<input type=\"checkbox\" id=\"statusUser\" name=\"statusUser\" value=\"1\">";
		echo "<label name=\"textLabel\" id=\"textLabel\" for\"statusUser\"> If this option is set, an appeal will be set to AWAITING_USER once the email is sent.</label><br>";
		echo "<input type=\"checkbox\" id=\"statusClose\" name=\"statusClose\" value=\"1\">";
		echo "<label name=\"textLabel\" id=\"textLabel\" for\"statusClose\"> If this option is set, an appeal will be set to CLOSED once the email is sent.</label><br>";
		echo "<input name=\"submit\" id=\"submit\" type=\"submit\" value=\"Save Template\" />\n";
		echo "</form>";
	}
} // closes else if(strcmp($_GET['id'], 'new') == 0){
else{
	$template = Template::getTemplateById($_GET['id']);
	$admin = verifyAccess($GLOBALS['ADMIN']);
	$name = ($admin & isset($_POST['name']) ? $_POST['name'] : $template->getName());
	$text = ($admin & isset($_POST['text']) ? $_POST['text'] : $template->getText());

	if($errors){
		displayError($errors);
	}
	else if(isset($_POST['submit'])){
		displaySuccess("Template updated successfully.");
	}
?>
	<table style="border:none; background:none;">
		<tr>
			<th style="text-align:left;">Template ID:</th>
			<td><?php echo $template->getId(); ?></td>
		</tr>
		<tr>
			<th style="text-align:left;">Last modified by:</th>
			<td><?php echo $template->getLastEditUser()->getUsername(); ?></td>
		</tr>
		<tr>
			<th style="text-align:left;">Last modified at:</th>
			<td><?php echo $template->getLastEditTime(); ?></td>
		</tr>
<?php 
	if($admin){
		echo "</table>\n";
		echo "<form name=\"editTemplate\" id=\"editTemplate\" method=\"POST\" action=\"tempMgmt.php?id=" . $template->getId() . "\">\n";
		echo "<label name=\"nameLabel\" id=\"nameLabel\" for=\"name\" class=\"required\">Name:</label> ";
		echo "<input name=\"name\" id=\"name\" type=\"text\" length=\"40\" value=\"" . $name . "\" />\n";
		echo "<label name=\"textLabel\" id=\"textLabel\" for=\"text\" class=\"required\">Text:</label>\n";
		echo "<textarea name=\"text\" id=\"text\" rows=\"12\" cols=\"60\">" . $text . "</textarea>\n";
		
		$checked = "";
		if ($template->getStatusUser()) {
			$checked = "CHECKED";
		}
		echo "<input type=\"checkbox\" " . $checked . " id=\"statusUser\" name=\"statusUser\" value=\"true\">";
		echo "<label name=\"textLabel\" id=\"textLabel\" for=\"statusUser\"> If this option is set, an appeal will be set to AWAITING_USER once the email is sent.</label><br>\n";
		
		$checked = "";
		if ($template->getStatusClose()) {
			$checked = "CHECKED";
		}
		echo "<input type=\"checkbox\" " . $checked . " id=\"statusClose\" name=\"statusClose\" value=\"true\">";
		echo "<label name=\"textLabel\" id=\"textLabel\" for=\"statusClose\"> If this option is set, an appeal will be set to CLOSED once the email is sent.</label>";
		
		echo "<table style=\"background:none; border:none; width:500px;\"><tr>\n";
		echo "<td style=\"text-align:left;\"><input name=\"submit\" id=\"submit\" type=\"submit\" value=\"Save Template\" /></td>\n";
		echo "<td style=\"text-align:right;\"><input name=\"delete\" id=\"delete\" type=\"submit\" value=\"Delete\" /></td>\n";
		echo "</tr></table>\n";
		echo "</form>";
	}
	else{
?>
		<tr>
			<th style="text-align:left;">Template name:</th>
			<td><?php echo $template->getName(); ?></td>
		</tr>
		<tr>
			<th style="text-align:left;">Template text:</th>
			<td><?php echo $template->getText(); ?></td>
		</tr>
	</table>
<?php 		
	} // closes else from if($admin)
} // closes else from else if(strcmp($_GET['id'], 'new') == 0){

echo "\n\n";

skinFooter();
?>