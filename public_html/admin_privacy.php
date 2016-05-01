<?php 

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('session.use_cookies', '1');

require_once('template.php');

//Template header()
skinHeader();

?>
<center><b>Unblock Ticket Request System Privacy Policy</b></center>
<p>Welcome to the Unblock Ticket Request System. We recognize that you are a volunteer administrator working to contribute
towards the world's largest free online encyclopedia. As such, we recognize that there may be some information
you'd rather keep private. We value that privacy and wish to assure you that we have taken steps to ensure
that by responding to unblock requests here, you are not at risk of exposing your identity on the internet. At the
same time, however, in order to ensure proper access, functionality and utilization of UTRS, we do need to collect certain
information that will assist us with those tasks.</p>

<h4>Wikimedia Labs Disclaimer</h4>
<p>By using this project, you agree that any private information you give to this project may be made publicly available and not be treated as confidential.

<p>By using this project, you agree that the volunteer administrators of this project will have access to any data you submit. This can include your IP address, your username/password combination for accounts created in Labs services, and any other information that you send. The volunteer administrators of this project are bound by the Wikimedia Labs Terms of Use, and are not allowed to share this information or use it in any non-approved way.

<p>Since access to this information is fundamental to the operation of Wikimedia Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.


<h4>What data do we collect, and why?</h4>

<p>This system records your IP Address and useragent data.  This information may be processed overseas and your
data will remain confidential.
It is important to note that this information is provided by your internet browser to any website 
you visit; it is not possible to confirm any specific person's identity with this information. We only
use this to prevent abuse of Wikipedia and UTRS and to ensure the proper functionality of UTRS.</p>

<p>We also require your email address so that we can notify you about important information relevant to you.</p>

<p>By creating an account at UTRS, you agree to provide this information and allow UTRS Developers and WMF Labs System Administrators 
to view it for the explicit purpose of maintaining the integrity and operation of the tool.</p>

<h4>How is this data stored, and who can see it?</h4>

<p>We store this data in a secure database, which is visible only to UTRS developers, all of whom
are identified to the Wikimedia Foundation, just as Checkusers and Oversighters are required to do. In order to assist 
with reviewing your block, this information is provided to UTRS volunteers as follows:</p>
<ul>
<li>Your email address will be only to you and UTRS Developers and WMF Labs System Administrator</li>
<li>Your useragent and IP address will only be visible to UTRS developers and WMF Labs System Administrators.</li>
<li>Your useragent and IP address addtionally be visible upon request from an approved member of the Wikimedia Foundation.
The use of this is rare, logged and monitered. You agree that any data released under this is subject to the Privacy Policy 
of the Wikimedia Foundation, and this privacy policy is nullified for the released data only.</li>
</ul>
<p>At no point will your data be provided to a third party for any purpose; furthermore, any private and sensitive data
regarding your account is removed after three months of it being in the tool. This 
removal process is automated, so you don't need to worry about anyone forgetting to hit the "delete" button.</p>
<p>Finally, we are not the Wikimedia Foundation.  We are a group of volunteers who have made our identities
known to the foundation.</p>



<h4>What are your rights with regard to this information?</h4>

<p>If you wish to see what information has been collected on you by this system, you may email the 
development team at <a href="mailto:utrs-developers@googlegroups.com?subject=UTRS%20information%20request">
utrs-developers@googlegroups.com</a> to request all information associated with your account.</p>

<p>If you do not wish for this information to be collected by UTRS, do not create an account or accept the active terms of use.</p>

<p>If you have already registered at UTRS and wish for your information to be deleted 
immediately, please email the development team at 
<a href="mailto:utrs-developers@googlegroups.com?subject=UTRS%20appeal%20removal%20request">utrs-developers@googlegroups.com</a> 
to have your account deleted from the database.</p>

<p>If you have any questions about this policy, please contact the development team at 
<a href="mailto:utrs-developers@googlegroups.com?subject=Privacy%20questions">utrs-developers@googlegroups.com</a>.</p>



<h4>What is your responsibility with the information provided by the interface?</h4>

<p>All information provided from the tool is to remain confidential and not to be shared outside the interface unless:
<ul>
<li>A WMF Staff member is using the data in a way consistent with the <a href="privacy.php">UTRS Privacy Policy</a> and the WMF Privacy Policy.</li>
<li>A CheckUser is storing data for an abusive account on CheckUser Wiki or similiar private medium.</li>
<li>A developer using it to diagnose the tool.</li>
<li>A developer assisting one of the above functions.</li>
</ul>




<?php 

skinFooter();

?>