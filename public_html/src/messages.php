<?php
class SystemMessages {
	public static $system = array (
			// Email syntax is very specific, be careful with editing these.
			"EmailFrom" => array (
					"en" => 'From: Unblock Review Team <noreply-unblock@toolserver.org>\r\n',
					"pt" => "" 
			),
			"EmailMIME" => array (
					"en" => 'MIME-Version: 1.0\r\n',
					"pt" => "" 
			),
			"EmailContentType" => array (
					"en" => 'Content-Type: text/html; charset=ISO-8859-1\r\n',
					"pt" => "" 
			),
			"AppealReturnEmail" => array (
					"en" => "Hello {{adminname}}, \n\n" . "This is a notification that an appeal has been returned to your queue.  " . "<b>DO NOT reply to this email</b> - it is coming from an unattended email address. If you wish " . "to review the reply, please click the link below.\n",
					"pt" => "" 
			),
			"ReviewResponse" => array (
					"en" => "Review response by clicking here",
					"pt" => "" 
			),
			"EmailSubject" => array (
					"en" => "Response to unblock appeal #",
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
			"StatusAwaitReviewers" => array (
					"en" => "Status change to AWAITING_REVIEWER",
					"pt" => ""
			),
			"StatusAwaitReviewers" => array (
					"en" => "Status change to AWAITING_REVIEWER",
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
			)
	);
}