<?php 

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('template.php');

//Template header()
skinHeader();

?>
<center><b>Unblock Ticket Request System Privacy Policy</b></center>
<p>Welcome to the Unblock Ticket Request System. We recognize that you are a volunteer working to contribute
towards the world's largest free online encyclopedia. As such, we recognize that there may be some information
you'd rather keep private. We value that privacy and wish to assure you that we have taken steps to ensure
that by requesting unblocking here, you are not at risk of exposing your identity on the internet. At the
same time, however, in order to properly process your unblock request, we do need to collect certain
information that will allow us to distinguish you from others editing from the same or a nearby location.</p>

<h4>Wikimedia Labs Disclaimer</h4>
<p>By using this project, you agree that any private information you give to this project may be made publicly available and not be treated as confidential.

<p>By using this project, you agree that the volunteer administrators of this project will have access to any data you submit. This can include your IP address, your username/password combination for accounts created in Labs services, and any other information that you send. The volunteer administrators of this project are bound by the Wikimedia Labs Terms of Use, and are not allowed to share this information or use it in any non-approved way.

<p>Since access to this information is fundamental to the operation of Wikimedia Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.


<h4>What data do we collect, and why?</h4>

<p>This system records your IP Address and useragent data.  This information may be processed overseas and your
data will remain confidential.
It is important to note that this information is provided by your internet browser to any website 
you visit; it is not possible to confirm any specific person's identity with this information. We only
use this to prevent abuse of Wikipedia and UTRS.</p>

<p>We also require your email address so that we can respond to you with questions and the result of 
your appeal.</p>

<p>By submitting an appeal at UTRS, you agree to provide this information and allow UTRS volunteers 
to view it for the explicit purpose of reviewing your block on the English Wikipedia.</p>

<h4>How is this data stored, and who can see it?</h4>

<p>We store this data in a secure database, which is visible only to UTRS developers, several of whom
are identified to the Wikimedia Foundation, just as Checkusers are required to do. In order to assist 
with reviewing your block, this information is provided to UTRS volunteers as follows:</p>
<ul>
<li>Your email address will be obscured for most people reviewing your block. All emails we send you 
will be sent through the system, so there is no need for volunteers to view your email address in 
full. As a result, only the domain of your address will be visible to most volunteers (for example, 
UTRSUser@gmail.com will display only as *****@gmail.com). The domain remains visible to assist volunteers 
in determining if you are a legitimate user of a school or business network, if you are editing from 
one. UTRS tool developers and WMF Staff will be able to see your full email address to ensure the tool is working 
properly or in emergency situations consistent with the Wikimedia Foundation Privacy Policy.</li>
<li>Your useragent and IP address will only be visible to UTRS developers and those reviewing the block that have 
access to the CheckUser tool on Wikipedia. CheckUsers need to see your useragent to help 
differentiate you from the person the block is intended for, and UTRS developers need to see this 
information to ensure the tool is working properly.</li>
<li>In very limited cases your IP address and useragent may be stored to prevent additional abuse of the tool or Wikipedia.</li>
<li>Your useragent and IP address additonally be visible upon request from an approved member of the Wikimedia Foundation.
The use of this is rare, logged and monitered. You agree that any data released under this is subject to the Privacy Policy 
of the Wikimedia Foundation, and this privacy policy is nullified for the released data only.</li>
<li>The Username (or IP address if you don't have an account), Appeal number, date of appeal, and status of appeal are publically
visible on the English Wikipedia only if your appeal is active. After that point the information is removed from the English Wikipedia.</li>
</ul>
<p>At no point will your data be provided to a third party for any purpose; furthermore, this 
information will be removed from our system no more than one week after your appeal is closed. This
removal process is automated, so you don't need to worry about anyone forgetting to hit the "delete"
button.</p>
<p>Finally, we are not the Wikimedia Foundation.  We are a group of volunteers who have made our identities
known to the foundation.</p>



<h4>What are your rights with regard to this information?</h4>

<p>If you wish to see what information has been collected on you by this system, you may email the 
development team at <a href="mailto:utrs-developers@googlegroups.com?subject=UTRS%20information%20request">
utrs-developers@googlegroups.com</a> to request all information associated with your appeal.</p>

<p>If you do not wish for this information to be collected by UTRS, you may appeal via your talk 
page on Wikipedia. Please note that if you appeal on Wikipedia, your IP address 
and useragent may be examined by any CheckUser, with cause, in accordance with the Wikimedia 
Foundation Privacy Policy.</p>

<p>If you have already entered an appeal at UTRS and wish for your information to be deleted 
immediately, please email the development team at 
<a href="mailto:utrs-developers@googlegroups.com?subject=UTRS%20appeal%20removal%20request">utrs-developers@googlegroups.com</a> 
to have your appeal deleted from the database. You will then need to appeal your block through one 
of the alternate venues mentioned above. Again, please note that this information will be automatically 
removed one week after your appeal is resolved.</p>

<p>If you have any questions about this policy, please contact the development team at 
<a href="mailto:utrs-developers@googlegroups.com?subject=Privacy%20questions">utrs-developers@googlegroups.com</a>.</p>
<?php 

skinFooter();

?>