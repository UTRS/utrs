<?php
class SystemMessages {
	public static $system = array (
			// Email syntax is very specific, be careful with editing these.
			"EmailFrom" => array (
					"en" => "From: Unblock Review Team <noreply-unblock@utrs.wmflabs.org>\r\n",
					"pt" => "" 
			),
			"EmailMIME" => array (
					"en" => "MIME-Version: 1.0\r\n",
					"pt" => "" 
			),
			"EmailContentType" => array (
					"en" => "Content-Type: text/html; charset=ISO-8859-1\r\n",
					"pt" => "" 
			),
			"AppealReturnEmail" => array (
					"en" => "Hello {{adminname}}, \n\nThis is a notification that an appeal has been returned to your queue. \n\n<b>DO NOT reply to this email</b> - it is coming from an unattended email address. If you wish\nto review the reply, please click the link below.\n",
					"pt" => "" 
			),
			"ReviewResponse" => array (
					"en" => "Review response by clicking here",
					"pt" => "" 
			),
			"EmailSubject" => array (
					"en" => "Response to unblock appeal #",
					"pt" => "" 
			),
			"ConfirmClose" => array (
					"en" => "Are you sure you want to close this appeal without sending a response?",
					"pt" => ""
			),
			"ConfirmAdmin" => array (
					"en" => "Do you really want to send this appeal to the tool admin queue?  Note: You will not be able to perform any other actions except comment until a tool admin can review it.  Please confirm this is really what you want to do.",
					"pt" => ""
			),
			"ConfirmCU" => array (
					"en" => "Please confirm you want to send this appeal to the checkuser queue",
					"pt" => ""
			)
	);
	public static $log = array (
			"StatusToCU" => array (
					"en" => 'Status change to AWAITING_CHECKUSER',
					"pt" => "" 
			),
			"CannotSetCU" => array (
					"en" => "Cannot set AWAITING_CHECKUSER status",
					"pt" => "" 
			),
			"AppealRelease" => array (
					"en" => "Released Appeal",
					"pt" => "" 
			),
			"AppealReturnUsers" => array (
					"en" => "Appeal reservation returned to tool users.",
					"pt" => "" 
			),
			"StatusAwaitReviewers" => array (
					"en" => "Status change to AWAITING_REVIEWER",
					"pt" => "" 
			), 
			"StatusAwaitUser" => array (
					"en" => "Status change to AWAITING_USER",
					"pt" => ""
			),
			"StatusAwaitReviewers" => array (
					"en" => "Status change to AWAITING_REVIEWER",
					"pt" => ""
			),
			"StatusOnHold" => array (
					"en" => "Status change to ON_HOLD",
					"pt" => ""
			),
			"StatusAwaitReviewers" => array (
					"en" => "Status change to AWAITING_REVIEWER",
					"pt" => ""
			),
			"StatusAwaitProxy" => array (
					"en" => "Status change to AWAITING_PROXY",
					"pt" => ""
			),
			"StatusAwaitAdmin" => array(
					"en" => "Status change to AWAITING_ADMIN",
					"pt" => ""
			),
			"AppealClosed" => array(
					"en" => "Closed",
					"pt" => ""
			),
			"NotifiedAdmin" => array(
					"en" =>	"Notified Admin",
					"pt" => ""
			),
			"NotifiedWMF" => array(
					"en" =>	"Emailed Wikimedia Foundation staff at ca@wikimedia.org",
					"pt" => ""
			)
	);
	public static $error = array (
			"AppealNotNumeric" => array (
					"en" => 'The appeal ID is not numeric',
					"pt" => "" 
			),
			"AlreadyReserved" => array (
					"en" => '"This request is already reserved or awaiting a checkuser or tool admin. If the person holding this ticket seems to be unavailable, ask a tool admin to break their reservation."',
					"pt" => "" 
			),
			"ReleaseFailed" => array (
					"en" => "Cannot release hold on appeal",
					"pt" => "" 
			),
			"FailReturnOldUser" => array (
					"en" => "Cannot return appeal to old handling tool user",
					"pt" => "" 
			), 
			"FailAwaitUser" => array (
					"en" => "Cannot return appeal to old handling tool user",
					"pt" => ""
			),
			"FailOnHold" => array (
					"en" => "Cannot assign STATUS_ON_HOLD status",
					"pt" => ""
			),
			"FailAwaitProxy" => array (
					"en" => "Cannot assign STATUS_AWAITING_PROXY status",
					"pt" => ""
			),
			"FailAwaitAdmin" => array(
					"en" => "Cannot assign STATUS_AWAITING_ADMIN status",
					"pt" => ""
			),
			"FailCloseAppeal" => array(
					"en" => "Unable to close the appeal",
					"pt" => ""
			),
			"TooladminsOnlyBan" => array(
					"en" => "Ban management is limited to tool administrators.",
					"pt" => ""
			),
			"FailResetAppeal" => array(
					"en" => "Unable to reset the appeal request",
					"pt" => ""
			),
			"NoCommentProvided" => array(
					"en" => "You have not entered a comment",
					"pt" => ""
			),
			"FailInvalid" => array(
					"en" => "Unable to mark appeal invalid",
					"pt" => ""
			),
			"CannotPostOPP" => array(
					"en" => "Unable to post Proxy check request automatically, you'll need to post it manually.",
					"pt" => ""
			),
			"DivertToACC" => array(
					"en" => "This appeal needs to be deferred to ACC instead of being posted to WP:OPP.",
					"pt" => ""
			)
	);
}
