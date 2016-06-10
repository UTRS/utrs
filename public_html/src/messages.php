<?php
class SystemMessages {
	public static $information = array (
			"LoggedInAs" => Array(
					"en" => "Logged in as",
					"pt" => "{pt LoggedInAs}"
			),
			"Blank" => array(
					"en" => "From: Unblock Review Team <noreply-unblock@utrs.wmflabs.org>\r\n",
					"pt" => "De: Equipe de Revisão de Desbloqueios <noreply-unblock@utrs.wmflabs.org>\r\n"
			),
			"CreateAccount" => array (
				"en" => "We may be able to create an account for you which you can use to avoid problems like this in the future. If you would like for us to make an account for you, please enter the username you'd like to use here.",
				"pt" => "{pt CreateAccount}"
			),
			"Welcome" => array (
				"en" => "Welcome to the Unblock Ticket Request System.",
				"pt" => "{pt Welcome}"
			),
			"AppealSucess" => array (
				"en" => "Your appeal has been recorded and is pending email address verification.  Please check your email inbox for a message from UTRS.  If you can't find such a message in your inbox, please check your junk mail folder.",
				"pt" => "{pt AppealSucess}"
			),
			"BlockIntro" => array (
				"en" => "If you are presently blocked from editing on Wikipedia (which you may verify by clicking <a href=\"http://en.wikipedia.org/w/index.php?title=Wikipedia:Sandbox&action=edit\">here</a>), you may fill out the form below to have an administrator review your block. Please complete all fields labelled in <span class=\"required\">red text</span>, as these are required in order for us to complete a full review of your block.</p>",
				"pt" => "{pt BlockIntro}"
			),
			"PageProtect" => array (
				"en" => "If you are having trouble editing a particular page or making a particular edit, but are able to edit the page linked in the previous paragraph, you may not be blocked, but instead could be having difficulty with <a href=\"http://en.wikipedia.org/wiki/Wikipedia:Protection policy\">page protection</a> or the <a href=\"http://en.wikipedia.org/wiki/Wikipedia:Edit filter\">edit filter</a>. For more information, and instructions on how to receive assistance, please see those links.",
				"pt" => "{pt PageProtect}"
			),
			"AgreePrivPol_appeal" => array (
				"en" => "By submitting this unblock request, you are consenting to allow us to collect information about your computer and that you agree with our <a href=\"privacy.php\">Privacy Policy</a>.  This information will in most cases allow us to distinguish you from any vandals editing from the same location. We do not store this information any longer than necessary, and do not share it with any third party. For more information, please see our <a href=\"privacy.php\">Privacy Policy.</a>",
				"pt" => "{pt AgreePrivPol_appeal}"
			),
			"AgreeAllTerms" => array (
				"en" => "By clicking \"Submit Appeal\", you agree to these terms and the terms of the <a href=\"privacy.php\">Privacy Policy</a> and the <a href=\"https://wikitech.wikimedia.org/wiki/Wikitech:Labs_Terms_of_use\" target=\"_new\">Wikimedia Labs Terms of Use</a>.",
				"pt" => "{pt AgreeAllTerms}"
			),
			"AppealSubmitInfo" => array (
				"en" => "Please remember that Wikipedia administrators are volunteers; it may take some time for your appeal to be reviewed, and a courteous appeal will be met with a courteous response. If you feel it is taking too long for your appeal to be reviewed, you can usually appeal your block on your user talk page (<a href=\"http://en.wikipedia.org/wiki/Special:Mytalk\">located here</a>) by copying this text and pasting it in a new section on the bottom of your page: <b><tt>{{unblock|1=your reason here}}</tt></b> Be sure to replace \"your reason here\" with your appeal.",
				"pt" => "{pt AppealSubmitInfo}"
			),
			"ReviewCount" => array (
				"en" => "Review count:",				"pt" => "{pt ReviewCount}"
			),
			"UnblockCount" => array (
				"en" => "Unblock count:",				"pt" => "{pt UnblockCount}"
			),
			"BecauseWMF" => array (
				"en" => "This email was generated automatically because an administrator requested WMF assistance via the UTRS interface.",	
				"pt" => "{pt BecauseWMF}"
			),
			"UTRSInfo" => array (
				"en" => "The Unblock Ticket Request System is a project hosted on the Wikimedia Labs intended to assist users with the unblock process. <br /> This software is licensed under the <a id=\"GPL\" href=\"http://www.gnu.org/copyleft/gpl.html\" target=\"_NEW\">GNU General Public License Version 3 or Later</a>.<br /> For questions or assistance with the Unblock Ticket Request System, please email our administration team at <a href=\"mailto:utrs-admins@googlegroups.com\">utrs-admins&#64;googlegroups.com</a>.<br />",
				"pt" => "{pt UTRSInfo}"
			),
			"Version" => array (
				"en" => "Version",
				"pt" => "{pt Version}"
			)
	,
		"BlameTParis" => array (
			"en" => "To UTRS users/appellants: This is likely not your fault, but an error on the part of a tool developer.",
			"pt" => "{pt BlameTParis}"
		));
	public static $system = array (
			// Email syntax is very specific, be careful with editing these. I've reverted some of the changes for technical reasons
			"EmailFrom" => array (
					"en" => "From: Unblock Review Team <noreply-unblock@utrs.wmflabs.org>\r\n",
					//Unblock Review Team can be translated here. Could we do that?
					"pt" => "From:Equipe de Revisão de Desbloqueios <noreply-unblock@utrs.wmflabs.org>\r\n" 
			),
			"EmailMIME" => array (
					//No translation should be need
					"en" => "MIME-Version: 1.0\r\n",
					"pt" => "MIME-Version: 1.0\r\n" 
			),
			"EmailContentType" => array (
					//No translation should be need
					"en" => "Content-Type: text/html; charset=ISO-8859-1\r\n",
					"pt" => "Content-Type: text/html; charset=ISO-8859-1\r\n" 
			),
			"AppealReturnEmail" => array (
					"en" => "Hello {{adminname}}, \n\nThis is a notification that an appeal has been returned to your queue. \n\n<b>DO NOT reply to this email</b> - it is coming from an unattended email address. If you wish\nto review the reply, please click the link below.\n",
					"pt" => "Olá {{adminname}}, \n\nEstá sendo notificado devido a uma solicitação ter retornado à sua fila. \n\n<b>NÃO responda a esse e-mail</b> - ele é proveniente de um endereço de e-mail automático. Se você deseja\n avaliar a resposta, por favor clique no link abaixo.\n" 
			),
			"ReviewResponse" => array (
					"en" => "Review response by clicking here",
					"pt" => "Revisar resposta clicando aqui" 
			),
			"EmailSubject" => array (
					"en" => "Response to unblock appeal #",
					"pt" => "Resposta do pedido de desbloqueio #" 
			),
			"ConfirmClose" => array (
					"en" => "Are you sure you want to close this appeal without sending a response?",
					"pt" => "Tem certeza de que quer encerrar este pedido sem enviar uma resposta?"
			),
			"ConfirmAdmin" => array (
					"en" => "Do you really want to send this appeal to the tool admin queue?  Note: You will not be able to perform any other actions except comment until a tool admin can review it.  Please confirm this is really what you want to do.",
					"pt" => "Você realmente quer enviar este pedido para a fila administrativa da ferramenta? Nota: Você não será capaz de executar qualquer outra ação, exceto comentar até um administrador do sistema revisá-lo. Por favor, confirme se deseja mesmo realizar essa ação."
			),
			"ConfirmCU" => array (
					"en" => "Please confirm you want to send this appeal to the checkuser queue",
					"pt" => "Por favor confirme que deseja enviar essa solicitação para a lista dos checkusers"
			),
			"SelectLang" => array (
					"en" => "Please select the wiki and language you are editing on.",
					"pt" => "{pt SelectLang}"
			),
			"YourIP" => array (
					"en" => "Your IP address",
					"pt" => "{pt YourIP}"
			),
			"HasBanned" => array (
					"en" => "has been banned",
					"pt" => "{pt HasBanned}"
			),
			"Until" => array (
					"en" => "until",
					"pt" => "{pt Until}"
			),
			"Indef" => array (
					"en" => "indefinitely",
					"pt" => "{pt Indef}"
			),
			"By" => array (
					"en" => "by",
					"pt" => "{pt by}"
			),
			"Reason" => array (
					"en" => "for the reason",
					"pt" => "{pt Reason}"
			),
			"DiffUName" => array (
					"en" => "You may be able to resubmit your appeal by selecting a different username.",
					"pt" => "{pt DiffUName}"
			),
			"StillAppeal" => array (
					"en" => "If you still wish to appeal your block, you may visit us on IRC at <a href=\"http://webchat.freenode.net/?channels=wikipedia-en-unblock\">#wikipedia-en-unblock</a> (if you haven't already done so) or email ArbCom at arbcom-l@lists.wikimedia.org.",
					"pt" => "{pt StillAppeal}"
			),
			"Uname" => array (
				"en" => "The username you entered",
				"pt" => "{pt Uname}"
			),
			"VerifyBlock" => array (
				"en" => "Please verify that you are blocked by following the instructions above.",
				"pt" => "{pt VerifyBlock}"
			),
			"IsAccountBlocked" => array (
				"en" => "Is it your account that is blocked?",
				"pt" => "{pt IsAccountBlocked}"
			),
			"IfHaveAccount" => array (
				"en" => "If you have an account, please select 'Yes' to \"Do you have an account on Wikipedia?\".",
				"pt" => "{pt IfHaveAccount}"
			),
			"VerifyEmailText" => array (
				"en" => "This is an automated message from the English Wikipedia Unblock Ticket Request System. In order for your appeal to be processed, you need to confirm that the email address you entered with your appeal is valid. To do this, simply click the link below.  If you did not file an appeal then simply do nothing, and the appeal will be deleted.",
				"pt" => "{pt VerifyEmailText}"
			),
			"NameOfAccount" => array (
				"en" => "What is the name of your account?",
				"pt" => "{pt NameOfAccount}"
			),
			"WhatIsBlocked" => array (
				"en" => "What has been blocked?",				"pt" => "{pt WhatIsBlocked}"
			),
			"MyAccount" => array (
				"en" => "My account",				"pt" => "{pt MyAccount}"
			),
			"MyIPorRange" => array (
				"en" => "My IP address or range (my account is not blocked)",				"pt" => "{pt MyIPorRange}"
			),
			"AssistBlock" => array (
				"en" => "For assistance with a block, please complete the form below:",
				"pt" => "{pt AssistBlock}"
			),
			"ReqEmail" => array (
				"en" => "What is your email address? <b>If you do not supply a deliverable email address, we will be unable to reply to your appeal and therefore it will not be considered.<br /><font color=red>Note: There is inconsistent delivery to Microsoft email services (such as: live.com, hotmail.com, outlook.com, etc.). If you use one of these services, we can not guarentee that you will recieve a confirmation email. Please avoid using these services.",				"pt" => "{pt ReqEmail}"
			),
			"HaveAccount" => array (
				"en" => "If you have an account, please select 'Yes' to 'Do you have an account on Wikipedia?'",				"pt" => "{pt HaveAccount}"
			),
			"WhyUnblock" => array (
				"en" => "Why do you believe you should be unblocked?",				"pt" => "{pt WhyUnblock}"
			),
			"WhatEdit" => array (
				"en" => "If you are unblocked, what articles do you intend to edit?",				"pt" => "{pt WhatEdit}"
			),
			"WhyBlockAffect" => array (
				"en" => "Why do you think there is a block currently affecting you? If you believe it's in error, tell us how.",				"pt" => "{pt WhyBlockAffect}"
			),
			"AnythingElse" => array (
				"en" => "Is there anything else you would like us to consider when reviewing your block?",				"pt" => "{pt AnythingElse}"
			),
			"SubmitAppeal" => array (
				"en" => "Submit Appeal",				"pt" => "{pt SubmitAppeal}"
			),
			"Userlink" => array (
				"en" => "User:",
				"pt" => "{pt Userlink}"
			),
			"ContribsLink" => array (
				"en" => "Special:Contributions/",
				"pt" => "{pt ContribsLink}"
			),
			"WMFStaffAssist" => array (
				"en" => "This is an email from the Unblock Ticket Request System.  Please do not reply to this email, replies will go to an unmonitored email box. <br><br> Assistance is requested from a Wikimedia Foundation staff member on ",
				"pt" => "{pt WMFStaffAssist}"
			),
			"TicketNum" => array (
				"en" => "UTRS Ticket #",
				"pt" => "{pt TicketNum}"
			),
			"ActiveUsers" => array (
				"en" => "Users active in the last five minutes:",
				"pt" => "{pt ActiveUsers}"
			),
			"SiteTitle" => array (
				"en" => "Unblock Ticket Request System",
				"pt" => "{pt SiteTitle}"
			),
			"AppealClose" => array (
					"en" => "Close",
					"pt" => "{pt AppealClose}"
			),
			"HeaderSiteTitle" => array (
				"en" => "Unblock Ticket<br />Request System",
				"pt" => "{pt HeaderSiteTitle}"
			),
			"Administration" => array (
				"en" => "Administration",
				"pt" => "{pt Administration}"
			),
			"ReleaseButton" => array (
				"en" => "Release",
				"pt" => "{pt ReleaseButton}"
			),
			"ReleaseButton" => array (
				"en" => "Release",
				"pt" => "{pt ReleaseButton}"
			),
			"ReserveButton" => array (
				"en" => "Reserve",
				"pt" => "{pt ReserveButton}"
			),
			"ResetButton" => array (
				"en" => "Reset to new",
				"pt" => "{pt ResetButton}"
			),
			"ReviewerButton" => array (
				"en" => "Back to Reviewer",
				"pt" => "{pt ReviewerButton}"
			),
			"ResponseButton" => array (
				"en" => "Await Response",
				"pt" => "{pt ResponseButton}"
			),
			"InvalidButton" => array (
				"en" => "Invalid",
				"pt" => "{pt InvalidButton}"
			),
			"CUButton" => array (
				"en" => "Send for Checkuser Review",
				"pt" => "{pt CUButton}"
			),
			"HoldButton" => array (
				"en" => "Place on hold",
				"pt" => "{pt HoldButton}"
			),
			"WMFButton" => array (
				"en" => "Request WMF Staff",
				"pt" => "{pt WMFButton}"
			),
			"ProxyButton" => array (
				"en" => "Request Proxy Check",
				"pt" => "{pt ProxyButton}"
			),
			"ToolAdminButton" => array (
				"en" => "Request Tool Administrator",
				"pt" => "{pt ToolAdminButton}"
			),
			"CloseAppeal" => array (
					"en" => "Close",
					"pt" => "{pt CloseAppeal}"
			),
			"AwaitProxyHook" => array(
					"en" => "Awaiting Proxy Check",
					"pt" => "{pt AwaitProxyHook}"				
			),
			"AwaitReviewerHook" => array(
					"en" => "Awaiting reviewer response",
					"pt" => "{pt AwaitReviewerHook}"
			),
			"AwaitAdminHook" => array(
					"en" => "Awaiting tool admin",
					"pt" => "{pt AwaitAdminHook}"
			),
			"AwaitUserHook" => array(
					"en" => "Awaiting user response",
					"pt" => "{pt AwaitUserHook}"
			),
			"BacklogHook" => array(
					"en" => "Backlog",
					"pt" => "{pt BacklogHook}"
			),
			"CheckUserNeededHook" => array(
					"en" => "Checkuser Needed",
					"pt" => "{pt CheckUserNeededHook}"
			),
			"ClosedRequestsHook" => array(
					"en" => "Last 5 closed requests",
					"pt" => "{pt ClosedRequestsHook}"
			),
			"cuNumberHook1" => array(
					"en" => "Number of appeals with CU data:",
					"pt" => "{pt cuNumberHook1}"
			),
			"cuNumberHook2" => array(
					"en" => "appeals have checkuser data in them.",
					"pt" => "{pt cuNumberHook2}"
			),
			"cuNumberHook3" => array(
					"en" => "Latest appeal with CU data at:",
					"pt" => "{pt cuNumberHook3}"
			),
			"cuNumberHook4" => array(
					"en" => "Run Now",
					"pt" => "{pt cuNumberHook4}"
			),
			"MyQueueHook" => array(
					"en" => "My Queue",
					"pt" => "{pt MyQueueHook}"
			),
			"NewRequestsHook" => array(
					"en" => "New Requests",
					"pt" => "{pt NewRequestsHook}"
			),
			"OnHoldHook" => array(
					"en" => "On Hold",
					"pt" => "{pt OnHoldHook}"
			),
			"UnverifiedHook" => array(
					"en" => "Awaiting email verification",
					"pt" => "{pt UnverifiedHook}"
			),
			"WaitingOnMeHook" => array(
					"en" => "Waiting on me",
					"pt" => "{pt WaitingOnMeHook}"
			)
	,
		"Argument" => array (
			"en" => "Argument",
			"pt" => "{pt Argument}"
		),
		"WasProvided" => array (
			"en" => "was provided to",
			"pt" => "{pt WasProvided}"
		),
		"When" => array (
			"en" => "when",
			"pt" => "{pt When}"
		),
		"WasExpected" => array (
			"en" => "was exptected",
			"pt" => "{pt WasExpected}"
		));
	public static $log = array (
			"StatusToCU" => array (
					"en" => 'Status change to AWAITING_CHECKUSER',
					"pt" => "Estado alterado para AWAITING_CHECKUSER" 
			),
			"CannotSetCU" => array (
					"en" => "Cannot set AWAITING_CHECKUSER status",
					"pt" => "Não foi  possível definir o estado AWAITING_CHECKUSER" 
			),
			"AppealRelease" => array (
					"en" => "Released Appeal",
					"pt" => "Pedido Feito" 
			),
			"AppealReserved" => array (
					"en" => "Reserved appeal",
					"pt" => "Pedido Reservado"
			),
			"AppealReturnUsers" => array (
					"en" => "Appeal reservation returned to tool users.",
					"pt" => "A reserva de pedidos retornou para os usuários da ferramenta" 
			),
			"StatusAwaitReviewers" => array (
					"en" => "Status change to AWAITING_REVIEWER",
					"pt" => "Estado modificado para AWAITING_REVIEWER" 
			), 
			"StatusAwaitUser" => array (
					"en" => "Status change to AWAITING_USER",
					"pt" => "Estado modificado para AWAITING_USER"
			),
			"StatusAwaitReviewers" => array (
					"en" => "Status change to AWAITING_REVIEWER",
					"pt" => "Estado modificado para AWAITING_REVIEWER"
			),
			"StatusOnHold" => array (
					"en" => "Status change to ON_HOLD",
					"pt" => "Estado modificado para ON_HOLD"
			),
			"StatusAwaitReviewers" => array (
					"en" => "Status change to AWAITING_REVIEWER",
					"pt" => "Estado modificado para AWAITING_REVIEWER"
			),
			"StatusAwaitProxy" => array (
					"en" => "Status change to AWAITING_PROXY",
					"pt" => "Estado modificado para AWAITING_PROXY"
			),
			"StatusAwaitAdmin" => array(
					"en" => "Status change to AWAITING_ADMIN",
					"pt" => "Estado modificado para AWAITING_ADMIN"
			),
			"AppealClosed" => array(
					"en" => "Closed",
					"pt" => "Fechado"
			),
			"RevealCUData" => array(
					"en" => "Revealed this appeals CU data: ",
					"pt" => "Revelada a informação de checkuser destes pedidos"
			),
			"RevealEmail" => array(
					"en" => "Revealed this appeals email: ",
					"pt" => "Revelado o email destes pedidos"
			),
			"RevealOS" => array(
					"en" => "Revealed this appeals oversighted information: ",
					"pt" => "Revelada a informação de supressão destes pedidos"
			),
			"NotifiedAdmin" => array(
					"en" =>	"Notified Admin",
					"pt" => "Administrador notificado"
			),
			"NotifiedWMF" => array(
					"en" =>	"Emailed Wikimedia Foundation staff at ca@wikimedia.org",
					"pt" => "Enviado email para funcionários da Wikimedia Foundation pelo ca@wikimedia.org"
			),
			"AppealCreated" => array (
				"en" => "Appeal Created",
				"pt" => "{pt AppealCreated}"
			),
			"WMFReq" => array (
				"en" => "WMF Assistance requested on unblock appeal #",
				"pt" => "{pt WMFReq}"
			)
	);
	public static $error = array (
			"AppealNotNumeric" => array (
					"en" => 'The appeal ID is not numeric',
					"pt" => "O ID do pedido não é numérico" 
			),
			"AlreadyReserved" => array (
					"en" => '"This request is already reserved or awaiting a checkuser or tool admin. If the person holding this ticket seems to be unavailable, ask a tool admin to break their reservation."',
					"pt" => "Essa solicitação já está reservada ou esperando um checkuser ou administrador do sistema. Se a pessoa a quem é destinado esse ticket estiver indisponível, peça a um administrador do sistema para desfazer sua reserva" 
			),
			"ReleaseFailed" => array (
					"en" => "Cannot release hold on appeal",
					"pt" => "Não foi possivel liberar o pedido em espera" 
			),
			"FailReturnOldUser" => array (
					"en" => "Cannot return appeal to old handling tool user",
					"pt" => "Não foi possível retornar o pedido ao seu gestor anterior" 
			), 
			"FailAwaitUser" => array (
					"en" => "Cannot return appeal to old handling tool user",
					"pt" => "Não foi possível retornar o pedido ao seu gestor anterior"
			),
			"FailOnHold" => array (
					"en" => "Cannot assign STATUS_ON_HOLD status",
					"pt" => "Não foi possível mudar o estado para STATUS_ON_HOLD"
			),
			"FailAwaitProxy" => array (
					"en" => "Cannot assign STATUS_AWAITING_PROXY status",
					"pt" => "Não foi possível mudar o estado para STATUS_AWAITING_PROXY"
			),
			"FailAwaitAdmin" => array(
					"en" => "Cannot assign STATUS_AWAITING_ADMIN status",
					"pt" => "Não foi possível mudar o estado para STATUS_AWAITING_ADMIN"
			),
			"FailCloseAppeal" => array(
					"en" => "Unable to close the appeal",
					"pt" => "Incapaz de fechar o pedido"
			),
			"TooladminsOnlyBan" => array(
					"en" => "Ban management is limited to tool administrators.",
					"pt" => "O gerenciamento de banimentos é limitado a administradores do sistema"
			),
			"FailResetAppeal" => array(
					"en" => "Unable to reset the appeal request",
					"pt" => "Não foi possível reiniciar o pedido"
			),
			"NoCommentProvided" => array(
					"en" => "You have not entered a comment",
					"pt" => "Você não escreveu um comentário"
			),
			"FailInvalid" => array(
					"en" => "Unable to mark appeal invalid",
					"pt" => "Não foi possível marcar pedido como inválido"
			),
			"NoRevealReason" => array(
					"en" => "No reveal reason was submitted. Please provide a reason.",
					"pt" => "Nenhuma razão de revelação foi dada. Por favor, forneça uma razão."
			),
            "CannotPostOPP" => array(
                    "en" => "Unable to post Proxy check request automatically, you'll need to post it manually.",
                    "pt" => "Não foi possível solicitar verificação de proxy automaticamente; você precisará solicitá-la manualmente"
            ),
            "DivertToACC" => array(
                    "en" => "This appeal needs to be deferred to ACC instead of being posted to WP:OPP.",
                    "pt" => "Este pedido precisa ser enviado ao ACC em vez de ser postado em WP:OPP"
            ),
            "NoAPILogin" => array(
                    "en" => "API login not yet implemented. The api is only available for logged in users for now.",
                    "pt" => "Login via API ainda não implementado. O PIA apenas está disponível para usuários registrados por enquanto."
            ),
            "BadParamAPI" => array(
                    "en" => "You have tried to call the UTRS api with bad parameters",
                    "pt" => "Você tentou acionar o API da UTRS com parâmetros incorretos"
            ),
			"LangError" => array (
					"en" => "EN: To use UTRS, it is required that you set a language and wiki to use. This is so that your appeal (or list of appeals for administrators) is selected from the right language. You can reset this at any time if you make a mistake. Once an appeal is filed in one language, it is impossible to change the language and wiki of that appeal.",
					"pt" => "PT: {pt lang-error}"
			),
			"BadCaptcha" => array(
					"en" => "The response you provided to the captcha was not correct. Please try again.",
					"pt" => "{pt BadCaptcha}"
			),
			"NotBlocked" => array (
				"en" => "is not currently blocked.",
				"pt" => "{pt NotBlocked}"
			),
			"AppealTalkpage" => array (
				"en" => "You are currently appealing your block on your talkpage. The UTRS team does not hear appeals already in the process of being reviewed.",
				"pt" => "{pt AppealTalkpage}"
			),
			"AlreadySubmitted" => array (
				"en" => "It looks like you have already submitted an appeal to UTRS. Please wait for that appeal to be reviewed. If you think this message is in error, please contact the email at the bottom of the page.",
				"pt" => "{pt AlreadySubmitted}"
			),
			"JSError" => array (
				"en" => "It looks like your browser either doesn't support Javascript, or Javascript is disabled. Elements of this form require Javascript to display properly. Please enable Javascript or use another browser to continue. Thank you!",
				"pt" => "{pt JSError}"
			),
			"NoResults" => array (
				"en" => "No results were returned for appeal ID",
				"pt" => "{pt NoResults}"
			),
			"EmailRequired" => array (
				"en" => "An email address is required in order to stay in touch with you about your appeal.",
				"pt" => "{pt EmailRequired}"
			),
			"AccountRequired" => array (
				"en" => "We need to know if you have an account on the English Wikipedia.",
				"pt" => "{pt AccountRequired}"
			),
			"AccountNameRequired" => array (
				"en" => "If you have an account, we need to know the name of your account.",
				"pt" => "{pt AccountNameRequired}"
			),
			"WhatBlockRequired" => array (
				"en" => "If you have an account, we need to know if you are appealing a direct block or an IP block.",
				"pt" => "{pt WhatBlockRequired}"
			),
			"WhichAdminRequired" => array (
				"en" => "We need to know which administrator placed your block.",
				"pt" => "{pt WhichAdminRequired}"
			),
			"NoReasonUnblock" => array (
				"en" => "You have not provided a reason why you wish to be unblocked.",
				"pt" => "{pt NoReasonUnblock}"
			),
			"UserReasonNeedUnblock" => array (
				"en" => "You have not told us what you think the reason you are blocked is.",
				"pt" => "{pt UserReasonNeedUnblock}"
			),
			"WhichEditsRequired" => array (
				"en" => "You have not told us what edits you wish to make once unblocked.",
				"pt" => "{pt WhichEditsRequired}"
			),
			"ValidEmailRequired" => array (
				"en" => "You have not provided a valid email address.",
				"pt" => "{pt ValidEmailRequired}"
			),
			"NoMailinator" => array (
				"en" => "Temporary email addresses, such as those issued by Mailinator, are not accepted.",
				"pt" => "{pt NoMailinator}"
			),
			"EmailBlacklisted" => array (
				"en" => "The email address you have entered is blacklisted. You must enter an email address that you own.",
				"pt" => "{pt EmailBlacklisted}"
			),
			"UsernameInvalid" => array (
				"en" => "The username you have entered is invalid. Usernames may not contain the characters",
				"pt" => "{pt UsernameInvalid}"
			),
			"InvalidStatus" => array (
				"en" => "The status you provided is invalid.",
				"pt" => "{pt InvalidStatus}"
			),
			"AlreadyReserved" => array (
				"en" => "This request is already reserved. If the person holding this ticket seems to be unavailable, ask a tool admin to break their reservation.",
				"pt" => "{pt AlreadyReserved}"
			),
			"EmailAlreadyVerified" => array (
				"en" => "The email address for this appeal has already been verified.",
				"pt" => "{pt EmailAlreadyVerified}"
			),
			"InvalidEmailToken" => array (
				"en" => "Invalid email confirmation token.  Please ensure that you have copied and pasted the verification URL correctly.",
				"pt" => "{pt InvalidEmailToken}"
			),
			"NoData" => array (
				"en" => "No unblock data found.",
				"pt" => "{pt NoData}"
			),
			"SiteNoticeError" => array (
					"en" => "An error occured when getting the sitenotice:",
					"pt" => "{pt SiteNoticeError}"
			),
			"HooksNoAppeals" => array (
					"en" => "No unblock requests in queue",
					"pt" => "{pt HooksNoAppeals}"
			),
			"NoResultsBanID" => array (
				"en" => "'No results were returned for ban ID '",
				"pt" => "{pt NoResultsBanID}"
			),
			"DurationPositive" => array (
				"en" => "Duration must be a positive number.",
				"pt" => "{pt DurationPositive}"
			),
			"UnitOfTime" => array (
				"en" => "You must select a unit of time if you set a duration.",
				"pt" => "{pt UnitOfTime}"
			),
			"ReasonRequired" => array (
				"en" => "You must provide a reason!",
				"pt" => "{pt ReasonRequired}"
			),
			"ReasonTooLarge" => array (
				"en" => "Your reason must be less than 1024 characters.",
				"pt" => "{pt ReasonTooLarge}"
			),
			"InvalidTarget" => array (
				"en" => "The target must be an IP address, email address, or valid Wikipedia username",
				"pt" => "{pt InvalidTarget}"
			),
			"ErrorAppeals" => array (
				"en" => "There were errors processing your unblock appeal:",
				"pt" => "{pt ErrorAppeals}"
			),
			"ActionNotPreformed" => array (
				"en" => "The action you requested could not be performed:",
				"pt" => "{pt ActionNotPreformed}"
			),
			"DataBaseError" => array (
				"en" => "A database error occured when attempting to process your request:",
				"pt" => "{pt DataBaseError}"
			),
			"AccessDenied" => array (
				"en" => "Access denied:",
				"pt" => "{pt AccessDenied}"
			),
			"ErrorPageLoad" => array (
				"en" => "An error occured while loading the page:",
				"pt" => "{pt ErrorPageLoad}"
			),
			"TryAgainLater" => array (
				"en" => "Please try again later; if the problem persists, contact a tool developer with this message. Thanks!",
				"pt" => "{pt TryAgainLater}"
			)
	);
	public static $links = array (
			"Home" => array (
					"en" => 'Home',
					"pt" => "{pt Home}"
			),
			"Stats" => array (
					"en" => "Statistics",
					"pt" => "{pt Stats}"
			),
			"TemplateManagement" => array (
					"en" => "Manage/View Templates",
					"pt" => "{pt TemplateManagement}"
			),
			"UserManagement" => array (
					"en" => "Tool Administration",
					"pt" => "{pt UserManagement}"
			),
			"Userlist" => array (
					"en" => "Userlist",
					"pt" => "{pt Userlist}"
			),
			"Search" => array (
					"en" => "Search",
					"pt" => "{pt Search}"
			),
			"Preferences" => array (
					"en" => "Preferences",
					"pt" => "{pt Preferences}"
			),
			"PrivPolAdmin" => array(
					"en" => "Privacy Policy",
					"pt" => "{pt PrivPolAdmin}"
			),
			"Jobs" => array(
					"en" => "UTRS Team",
					"pt" => "{pt Jobs}"
			),
			"Logout" => array(
					"en" => "Logout",
					"pt" => "{pt Logout}"
			),
			"AppealBlock" => array(
					"en" => "Appeal a Block",
					"pt" => "{pt AppealBlock}"
			),
			"GTAB" => array(
					"en" => "Guide to Appealing Blocks",
					"pt" => "{pt GTAB}"
			),
			"GTABLink" => array(
					"en" => "http://en.wikipedia.org/wiki/Wikipedia:Guide_to_appealing_blocks",
					"pt" => "{pt GTABLink}"
			),
			"Login" => array(
					"en" => "Admins: Log in to review requests",
					"pt" => "{pt Login}"
			),
			"Register" => array(
					"en" => "Admins: Request an account",
					"pt" => "{pt Register}"
			),
			"PrivPol" => array(
					"en" => "Privacy Policy",
					"pt" => "{pt PrivPol}"
			),
			"BanManagement" => array (
				"en" => "Ban Management",
				"pt" => "{pt BanManagement}"
			),
			"HookManagement" => array (
				"en" => "Hook Management",
				"pt" => "{pt HookManagement}"
			),
			"SitenoticeManagement" => array (
				"en" => "Sitenotice Management",
				"pt" => "{pt SitenoticeManagement}"
			),
			"MassEmail" => array (
				"en" => "Send Mass Email",
				"pt" => "{pt MassEmail}"
			)
	);
	public static $tos = array (
			"Welcome" => array (
					"en" => "Welcome",
					"pt" => "Bem-vindo"
			),
			"TOSAccept" => array (
					"en" => "Thank you, your account has been updated.  Click <a href=\"home.php\">here</a> to go to the homepage.",
					"pt" => "Obrigado. Sua conta foi atualizada. Clique <a href=\"home.php\">aqui</a> para ir à página inicial."
			),
			"NewTerms" => array (
					"en" => "With the development of UTRS, this project occasionally requires a modified terms of service than when you initially registered.  To continue to participate in this system, for which your time is greatly appreciated, we require you to first accept these new terms.",
					"pt" => "Com o desenvolvimento do UTRS, este projeto ocasionalmente requer um termo de serviço diferente daquele de quando você se registrou. Para continuar a participar desse sistema, pelo qual seu tempo é bastante apreciado, nós solicitamos que aceite antes esses novos termos."
			),
			"ReviewToAccept" => array (
					"en" => "Please review the following policies and click \"I accept\" below to continue:",
					"pt" => "Por favor, revise as regras seguintes e clique em \"Eu aceito\" abaixo para continuar:"
			),
			"UTRSuserprivpol" => array (
					"en" => "UTRS Member Privacy Policy and Duties",
					"pt" => "Política de Privacidade e Obrigações dos Membros do UTRS"
			),
			"WMFLabsToS" => array (
					"en" => "Wikimedia Labs terms of service",
					"pt" => "Termos de serviço do Wikimedia Labs"
			),
			"LabsGeneralWarn" => array (
					"en" => "Warning: Do not use the Labs Project (this site) if you do not agree to the following: information shared with the Labs Project, including usernames and passwords, will be made available to volunteer administrators and may not be treated confidentially.",
					"pt" => "Aviso: Não use o Projeto Labs (este site) se não concorda com o seguinte: informação compartilhada com o Projeto Labs, incluindo nomes de usuários e senhas, serão disponibilizadas para administradores voluntários e podem não ser tratadas com confidencialidade."
			),
			"LabsDisclaimer" => array(
					"en" => "<p>Volunteers may have full access to the systems hosting the projects, allowing them access to any data or other information you submit. <p>As a result, use of your real Wikimedia credentials is highly discouraged in wmflabs.org projects. You should use a different password for your account than you would on projects like Wikipedia, Commons, etc. <p>By creating an account in this project and/or using other Wikimedia Labs Services, you agree that the volunteer administrators of this project will have access to any data you submit. <p>Since access to this information by volunteers is fundamental to the operation of Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.",
					"pt" => "<p>Voluntários podem ter acesso total ao sistema que controla os projetos, permitindo a eles acesso a qualquer dado ou outra informação que você fornecer. <p>Como resultado, o uso de suas reais credenciais da Wikimedia é altamente desencorajado nos projetos do wmflabs.org. Você deve usar uma senha diferente da senha que usa em projetos como Wikipédia, Commons, etc. <p>Ao criar uma conta neste projeto e/ou usar outros serviços do Wikimedia Labs, você concorda que os administradores voluntários deste projeto tenham acesso a qualquer dado que fornecer. <p>Já que o acesso a essa informação por voluntários é fundamental para a operação do Labs, estes termos de uso de seus dados se sobrepõem expressamente à Política de Privacidade da Wikimedia Foundation, já que se relaciona ao uso e acesso à sua informação pessoal."
			),
			"ToSAgree" => array(
					"en" => "If you agree check here and click submit:",
					"pt" => "Se concorda, marque aqui e clique em \"submeter\":"
			),
			"IAccept" => array(
					"en" => "I accept",
					"pt" => "Eu aceito"
			)
	);
	public static $privpol_all = array (
			"Clarity" => array(
					//EN version will never actually be shown
					"en" => "For clarity, if there are differences between the English version and the {Your language} version, the English version will be used.",
					"pt" => "Para mais clareza, se houver diferenças entre a versão em inglês e a versão em português, a versão em inglês será usada."
			),
			"UTRSPrivPol" => array (
					"en" => "Unblock Ticket Request System Privacy Policy",
					"pt" => "Política de Privacidade do Unblock Ticket Request System"
			),
			"WikimediaLabsDisclaimerTitle" => array (
					"en" => "Wikimedia Labs Disclaimer",
					"pt" => "Aviso Legal do Wikimedia Labs"
			),
			"WhatCollectTitle" => array (
					"en" => "What data do we collect, and why?",
					"pt" => "Qual dado coletamos e por que?"
			),
			"DataStoreTitle" => array (
					"en" => "How is this data stored, and who can see it?",
					"pt" => "Como este dado é armazenado e quem pode vê-lo?"
			),
			"UserRightsTitle" => array(
					"en" => "What are your rights with regard to this information?",
					"pt" => "Quais são seus direitos com relação a esta informação?"
			),
			"ResponsibilityTitle" => array(
					"en" => "What is your responsibility with the information provided by the interface?",
					"pt" => "Qual a sua responsabilidade com a informação fornecida pela interface?"
			),
			"WikimediaLabsDisclaimer" => array (
					"en" => "<p>By using this project, you agree that any private information you give to this project may be made publicly available and not be treated as confidential. <p>By using this project, you agree that the volunteer administrators of this project will have access to any data you submit. This can include your IP address, your username/password combination for accounts created in Labs services, and any other information that you send. The volunteer administrators of this project are bound by the Wikimedia Labs Terms of Use, and are not allowed to share this information or use it in any non-approved way. <p>Since access to this information is fundamental to the operation of Wikimedia Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.",
					"pt" => "<p>Ao usar este projeto, você concorda que toda informação privada que fornecer a este projeto pode ser tornada pública e não ser tratada com confidencialidade. <p>Ao usar este projeto, você concorda que o administrador voluntário deste projeto tenha a qualquer informação que enviar. Isso inclui o seu endereço de IP, seu nome de usuário/senha que usar em contas do Labs e qualquer outra informação que enviar. Os administradores voluntários deste projeto estão sujeitos aos Termos de Uso do Wikimedia Labs e não têm permissão de compartilhar essas informações ou usá-las de uma forma que não tenha sido aprovada. <p>Já que o acesso a essa informação é fundamental para a operação do Wikimedia Labs, estes termos ligados ao uso de seus dados se sobrepõem expressamente à Política de Privacidade da Wikimedia Foundation, já que se relaciona ao uso e acesso aos seus dados pessoais."
			)
	);
	public static $privpol_admin = array (
			"StepsForPrivacy" => array (
					"en" => "Welcome to the Unblock Ticket Request System. We recognize that you are a volunteer administrator working to contribute towards the world's largest free online encyclopedia. As such, we recognize that there may be some information you'd rather keep private. We value that privacy and wish to assure you that we have taken steps to ensure that by responding to unblock requests here, you are not at risk of exposing your identity on the internet. At the same time, however, in order to ensure proper access, functionality and utilization of UTRS, we do need to collect certain information that will assist us with those tasks.",
					"pt" => "{pt StepsForPrivacy}"
			),
			"WhatCollect" => array (
					"en" => "<p>This system records your IP Address and useragent data.  This information may be processed overseas and your data will remain confidential. It is important to note that this information is provided by your internet browser to any website you visit; it is not possible to confirm any specific person's identity with this information. We only use this to prevent abuse of Wikipedia and UTRS and to ensure the proper functionality of UTRS.</p> <p>We also require your email address so that we can notify you about important information relevant to you.</p> <p>By creating an account at UTRS, you agree to provide this information and allow UTRS Developers and WMF Labs System Administrators to view it for the explicit purpose of maintaining the integrity and operation of the tool.</p>",
					"pt" => "{pt WhatCollect}"
			),
			"DataStore" => array(
					"en" => "<p>We store this data in a secure database, which is visible only to UTRS developers, all of whom are identified to the Wikimedia Foundation, just as Checkusers and Oversighters are required to do. In order to assist with reviewing your block, this information is provided to UTRS volunteers as follows:</p> <ul> <li>Your email address will be only to you and UTRS Developers and WMF Labs System Administrator</li> <li>Your useragent and IP address will only be visible to UTRS developers and WMF Labs System Administrators.</li> <li>Your useragent and IP address addtionally be visible upon request from an approved member of the Wikimedia Foundation. The use of this is rare, logged and monitered. You agree that any data released under this is subject to the Privacy Policy of the Wikimedia Foundation, and this privacy policy is nullified for the released data only.</li> </ul> <p>At no point will your data be provided to a third party for any purpose; furthermore, any private and sensitive data regarding your account is removed after three months of it being in the tool. This removal process is automated, so you don't need to worry about anyone forgetting to hit the \"delete\" button.</p> <p>Finally, we are not the Wikimedia Foundation.  We are a group of volunteers who have made our identities known to the foundation.</p>",
					"pt" => "{pt DataStore}"
			),
			"UserRights" => array(
					"en" => "<p>If you wish to see what information has been collected on you by this system, you may email the development team at <a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20information%20request\"> utrs-developers@googlegroups.com</a> to request all information associated with your account.</p> <p>If you do not wish for this information to be collected by UTRS, do not create an account or accept the active terms of use.</p> <p>If you have already registered at UTRS and wish for your information to be deleted immediately, please email the development team at <a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20appeal%20removal%20request\">utrs-developers@googlegroups.com</a> to have your account deleted from the database.</p> <p>If you have any questions about this policy, please contact the development team at <a href=\"mailto:utrs-developers@googlegroups.com?subject=Privacy%20questions\">utrs-developers@googlegroups.com</a>.</p>",
					"pt" => "{pt UserRights}"
			),
			"Responsibility" => array(
					"en" => "<p>All information provided from the tool is to remain confidential and not to be shared outside the interface unless: <ul> <li>A WMF Staff member is using the data in a way consistent with the <a href=\"privacy.php\">UTRS Privacy Policy</a> and the WMF Privacy Policy.</li> <li>A CheckUser is storing data for an abusive account on CheckUser Wiki or similiar private medium.</li> <li>A developer using it to diagnose the tool.</li> <li>A developer assisting one of the above functions.</li> </ul>",
					"pt" => "{pt Responsibility}"
			)
	);
	public static $privpol_user = array (
			"StepsForPrivacy" => array (
					"en" => "Welcome to the Unblock Ticket Request System. We recognize that you are a volunteer working to contribute towards the world's largest free online encyclopedia. As such, we recognize that there may be some information you'd rather keep private. We value that privacy and wish to assure you that we have taken steps to ensure that by requesting unblocking here, you are not at risk of exposing your identity on the internet. At the same time, however, in order to properly process your unblock request, we do need to collect certain information that will assist us with allow us to distinguish you from others editing from the same or a nearby location.",
					"pt" => "{pt StepsForPrivacy_user}"
			),
			"WhatCollect" => array (
					"en" => "<p>This system records your IP Address and useragent data.  This information may be processed overseas and your data will remain confidential. It is important to note that this information is provided by your internet browser to any website you visit; it is not possible to confirm any specific person's identity with this information. We only use this to prevent abuse of Wikipedia and UTRS.</p> <p>We also require your email address so that we can respond to you with questions and the result of your appeal.</p> <p>By submitting an appeal at UTRS, you agree to provide this information and allow UTRS volunteers to view it for the explicit purpose of reviewing your block on the English Wikipedia.</p>",
					"pt" => "{pt WhatCollect_user}"
			),
			"DataStore" => array(
					"en" => "<p>We store this data in a secure database, which is visible only to UTRS developers, all of whom are identified to the Wikimedia Foundation, just as Checkusers and Oversighters are required to do. In order to assist with reviewing your block, this information is provided to UTRS volunteers as follows:</p> <ul> <li>Your email address will be obscured for most people reviewing your block. All emails we send you will be sent through the system, so there is no need for volunteers to view your email address in full. As a result, only the domain of your address will be visible to most volunteers (for example, UTRSUser@gmail.com will display only as *****@gmail.com). The domain remains visible to assist volunteers in determining if you are a legitimate user of a school or business network, if you are editing from one. UTRS tool developers and WMF Staff will be able to see your full email address to ensure the tool is working properly or in emergency situations consistent with the Wikimedia Foundation Privacy Policy.</li> <li>Your useragent and IP address will only be visible to UTRS developers and those reviewing the block that have access to the CheckUser tool on Wikipedia. CheckUsers need to see your useragent to help differentiate you from the person the block is intended for, and UTRS developers need to see this information to ensure the tool is working properly.</li> <li>In very limited cases your IP address and useragent may be stored to prevent additional abuse of the tool or Wikipedia.</li> <li>Your useragent and IP address addtionally be visible upon request from an approved member of the Wikimedia Foundation. <li>The Username (or IP address if you don't have an account), Appeal number, date of appeal, and status of appeal are publically visible on the English Wikipedia only if your appeal is active. After that point the information is removed from the English Wikipedia.</li> The use of this is rare, logged and monitered. You agree that any data released under this is subject to the Privacy Policy of the Wikimedia Foundation, and this privacy policy is nullified for the released data only.</li> </ul> <p>At no point will your data be provided to a third party for any purpose; furthermore, this information will be removed from our system no more than one week after your appeal is closed. This removal process is automated, so you don't need to worry about anyone forgetting to hit the \"delete\" button.</p> <p>Finally, we are not the Wikimedia Foundation.  We are a group of volunteers who have made our identities known to the foundation.</p>",
					"pt" => "{pt DataStore_user}"
			),
			"UserRights" => array(
					"en" => "<p>If you wish to see what information has been collected on you by this system, you may email the development team at <a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20information%20request\">utrs-developers@googlegroups.com</a> to request all information associated with your appeal.</p> <p>If you do not wish for this information to be collected by UTRS, you may appeal via your talk page on Wikipedia. Please note that if you appeal on Wikipedia, your IP address and useragent may be examined by any CheckUser, with cause, in accordance with the Wikimedia Foundation Privacy Policy.</p> <p>If you have already entered an appeal at UTRS and wish for your information to be deleted immediately, please email the development team at <a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20appeal%20removal%20request\">utrs-developers@googlegroups.com</a> to have your appeal deleted from the database. You will then need to appeal your block through one of the alternate venues mentioned above. Again, please note that this information will be automatically removed one week after your appeal is resolved.</p> <p>If you have any questions about this policy, please contact the development team at <a href=\"mailto:utrs-developers@googlegroups.com?subject=Privacy%20questions\">utrs-developers@googlegroups.com</a>.</p>",
					"pt" => "{pt UserRights_user}"
			)
	);
}
?>
