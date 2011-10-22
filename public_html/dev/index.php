<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
<title>Insert title here</title>
</head>
<body>
<div id="header">
Unblock<br />
Ticket<br />
Request<br />
System<br />
</div>
<div id="subheader">
<a id="GAB" href="http://en.wikipedia.org/wiki/Wikipedia:Guide_to_appealing_blocks">Guide to Appealing Blocks</a>
<a id="loginLink" href="login.php">Admins: Log in to review requests</a>
</div>
<div id="main">
<center><b>Welcome to the Unblock Ticket Request System.</b></center>

<p>If you are presently blocked from editing on Wikipedia (which you may verify by 
clicking <a href="http://en.wikipedia.org/w/index.php?title=Wikipedia:Sandbox?action=edit">here</a>, you may fill out
the form below to have an administrator review your block. Please complete all fields labelled in 
<span class="required">red text</span>, as these are required in order for us to complete a full review of your block.</p>

<p>If you are having trouble editing a particular page or making a particular edit, but are able to edit the page
linked in the previous paragraph, you may not be blocked, but instead could be having difficulty with 
<a href="http://en.wikipedia.org/wiki/Wikipedia:Protection policy">page protection</a> or the 
<a href="http://en.wikipedia.org/wiki/Wikipedia:Edit filter">edit filter</a>. For more information, and instructions on
how to receive assistance, please see those links.</p>

<p><b>For assistance with a block, please complete the form below:</b></p>

<form name="unblockAppeal" id="unblockAppeal" action="index.php" method="POST">
<label id="accountYNLabel" for="registered" class="required">Do you have an account on Wikipedia?</label> Yes <input type="radio" name="registered" value="true" /> No <input type="radio" name="registered" value="false" /><br />
<label id="accountNameLabel" for="accountName" class="required">What is the name of your account?</label> <input type="text" name="accountName" value=""/><br />
<label id="accountYNLabel" for="autoBlock" class="required">What has been blocked?</label> My account <input type="radio" name="autoBlock" value="false" /> My IP address (my account is not blocked) <input type="radio" name="autoBlock" value="true" /><br />
<label id="appealLabel" for="appeal" class="required">Why do you believe you should be unblocked?</label><br />
<textarea name="appeal" rows="5" cols="50"> </textarea><br />
<label id="editsLabel" for="edits" class="required">If you are unblocked, what articles to you intend to edit?</label><br />
<textarea name="edits" rows="5" cols="50"> </textarea><br />
<label id="otherInfoLabel" for="otherInfo">is there anything else you would like us to consider when reviewing your block?</label><br />
<textarea name="otherInfo" rows="3" cols="50"> </textarea><br />
<label id="desiredAccountNameLabel" for="desiredAccountName">We may be able to create an account for you which you can use to avoid problems like this in the future. <br />
If you would like for us to make an account for you, please enter the username you'd like to use here.</label> <input type="text" name="desiredAccountName" value=""/><br />

CAPTCHA GOES HERE

<input type="submit" value="Submit Appeal"/>
</form>

<p>Please remember that Wikipedia adminstrators are volunteers; it may take some time for your appeal to be reviewed, <br />
and a courteous appeal will meet with a courteous response. If you feel it is taking too long for your appeal to be reviewed, <br/>
you can usually appeal your block on your user talk page (<a href="http://en.wikipedia.org/wiki/Special:Mytalk">located here</a>)<br />
by copying this text and pasting it in a new section on the bottom of your page. Be sure to replace "your reason here"<br />
with your appeal: <b><tt>{{unblock|1=your request here}}</tt></b></p>
</div>
<div id="footer">
The Unblock Ticket Request System is a project hosted on the Wikimedia Toolserver intended to assist
users with the unblock process on the English Wikipedia. <br />
This project is licensed under the 
<a id="GPL" href="http://www.gnu.org/copyleft/gpl.html">GNU General Public License Version 3 or Later.</a><br />
For questions or assistance with the Unblock Ticket Request System, please email our development team at 
<a href="mailto:unblock@toolserver.org">unblock AT toolserver DOT org</a><br />
</div>
</body>
</html>