<?php
class SystemMessages {
	public static $system = array (
			// Email syntax is very specific, be careful with editing these. I've reverted some of the changes for technical reasons
			"EmailFrom" => array (
					"en" => "From: Unblock Review Team <noreply-unblock@utrs.wmflabs.org>\r\n",
					//Unblock Review Team can be translated here. Could we do that?
					"pt" => "From: Unblock Review Team <noreply-unblock@utrs.wmflabs.org>\r\n" 
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
					"pt" => "Olá {{adminname}}, \n\nEstá sendo notificado devido a uma solicitação ter retornado a sua queue. \n\n<b>NÃO responda esse e-mail</b> - Ele é proveniente de um endereço de e-mail automático. Ele é proveniente de um endereço de e-mail automático. Se você deseja  avaliar a resposta, por favor clique no link abaixo.\n" 
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
					"pt" => "Você realmente quer enviar este pedido para um administrador do sistema? Nota: Você não será capaz de executar qualquer outra ação, exceto comentários até um administrador do sistema revisa-lo. Por favor, confirme se deseja mesmo realizar essa ação."
			),
			"ConfirmCU" => array (
					"en" => "Please confirm you want to send this appeal to the checkuser queue",
					"pt" => "Por favor confirme que deseja enviar essa solicitação para a checkuser queue"
			)
	);
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
			"AppealReserve" => array (
					"en" => "Reserved appeal",
					"pt" => "Pedido Reservado"
			),
			"AppealReturnUsers" => array (
					"en" => "Appeal reservation returned to tool users.",
					"pt" => "A reserva de pedidos retornou para as ferramentas de usuário" 
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
					"pt" => "Mostrar esse pedido nos dados de CU"
			)
			,
			"RevealEmail" => array(
					"en" => "Revealed this appeals email: ",
					"pt" => "Mostrar essa solicitação de e-mail"
			),
			"RevealOS" => array(
					"en" => "Revealed this appeals oversighted information: ",
					"pt" => "Mostrar  informações suprimidas desse pedido"
			),
			"NotifiedAdmin" => array(
					"en" =>	"Notified Admin",
					"pt" => "Notificar Adm"
			),
			"NotifiedWMF" => array(
					"en" =>	"Emailed Wikimedia Foundation staff at ca@wikimedia.org",
					"pt" => ""
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
					"pt" => "Não foi possível retornar o pedido ao usuário anterior" 
			), 
			"FailAwaitUser" => array (
					"en" => "Cannot return appeal to old handling tool user",
					"pt" => "Não foi possível retornar o pedido ao usuário anterior"
			),
			"FailOnHold" => array (
					"en" => "Cannot assign STATUS_ON_HOLD status",
					"pt" => "Não foi possível manter o estado STATUS_ON_HOLD"
			),
			"FailAwaitProxy" => array (
					"en" => "Cannot assign STATUS_AWAITING_PROXY status",
					"pt" => "Não foi possível manter o estado STATUS_AWAITING_PROXY"
			),
			"FailAwaitAdmin" => array(
					"en" => "Cannot assign STATUS_AWAITING_ADMIN status",
					"pt" => "Não foi possível manter o estado STATUS_AWAITING_ADMIN"
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
					"pt" => " "
			),
			"NoCommentProvided" => array(
					"en" => "You have not entered a comment",
					"pt" => ""
			),
			"FailInvalid" => array(
					"en" => "Unable to mark appeal invalid",
					"pt" => ""
			),
			"NoRevealReason" => array(
					"en" => "No reveal reason was submitted. Please provide a reason.",
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
	public static $tos = array (
			"Welcome" => array (
					"en" => "Welcome",
					"pt" => ""
			),
			"TOSAccept" => array (
					"en" => "Thank you, your account has been updated.  Click <a href=\"home.php\">here</a> to go to the homepage.",
					"pt" => ""
			),
			"NewTerms" => array (
					"en" => "With the development of UTRS, this project occasionally requires a modified terms of service than when you initially registered.  To continue to participate in this
system, for which your time is greatly appreciated, we require you to first accept these new terms.",
					"pt" => ""
			),
			"ReviewToAccept" => array (
					"en" => "Please review the following policies and click \"I accept\" below to continue:",
					"pt" => ""
			),
			"UTRSuserprivpol" => array (
					"en" => "UTRS Member Privacy Policy and Duties",
					"pt" => ""
			),
			"WMFLabsToS" => array (
					"en" => "Wikimedia Labs terms of service",
					"pt" => ""
			),
			"LabsGeneralWarn" => array (
					"en" => "Warning: Do not use the Labs Project (this site) if you do not agree to the following: information shared with the Labs Project, including usernames and passwords, will be made available to volunteer administrators and may not be treated confidentially.",
					"pt" => ""
			),
			"LabsDisclaimer" => array(
					"en" => "<p>Volunteers may have full access to the systems hosting the projects, allowing them access to any data or other information you submit.
<p>As a result, use of your real Wikimedia credentials is highly discouraged in wmflabs.org projects. You should use a different password for your account than you would on projects like Wikipedia, Commons, etc.
<p>By creating an account in this project and/or using other Wikimedia Labs Services, you agree that the volunteer administrators of this project will have access to any data you submit.
<p>Since access to this information by volunteers is fundamental to the operation of Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.",
					"pt" => ""
			),
			"ToSAgree" => array(
					"en" => "If you agree check here and click submit:",
					"pt" => ""
			),
			"IAccept" => array(
					"en" => "I accept",
					"pt" => ""
			),
			"Clarity" => array(
					//EN version will never actually be shown
					"en" => "For clarity, if there are differences between the English version and the {Your language} version, the English version will be used.",
					"pt" => ""
			)
	);
	public static $privpol_admin = array (
			"Clarity" => array(
					//EN version will never actually be shown
					"en" => "For clarity, if there are differences between the English version and the {Your language} version, the English version will be used.",
					"pt" => ""
			),
			"UTRSPrivPol" => array (
					"en" => "Unblock Ticket Request System Privacy Policy",
					"pt" => ""
			),
			"StepsForPrivacyAdmin" => array (
					"en" => "Welcome to the Unblock Ticket Request System. We recognize that you are a volunteer administrator working to contribute
towards the world's largest free online encyclopedia. As such, we recognize that there may be some information
you'd rather keep private. We value that privacy and wish to assure you that we have taken steps to ensure
that by responding to unblock requests here, you are not at risk of exposing your identity on the internet. At the
same time, however, in order to ensure proper access, functionality and utilization of UTRS, we do need to collect certain
information that will assist us with those tasks.",
					"pt" => ""
			),
			"WikimediaLabsDisclaimerTitle" => array (
					"en" => "Wikimedia Labs Disclaimer",
					"pt" => ""
			),
			"WikimediaLabsDisclaimer" => array (
					"en" => "<p>By using this project, you agree that any private information you give to this project may be made publicly available and not be treated as confidential.

<p>By using this project, you agree that the volunteer administrators of this project will have access to any data you submit. This can include your IP address, your username/password combination for accounts created in Labs services, and any other information that you send. The volunteer administrators of this project are bound by the Wikimedia Labs Terms of Use, and are not allowed to share this information or use it in any non-approved way.

<p>Since access to this information is fundamental to the operation of Wikimedia Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.",
					"pt" => ""
			),
			"WhatCollectTitle" => array (
					"en" => "What data do we collect, and why?",
					"pt" => ""
			),
			"WhatCollect" => array (
					"en" => "<p>This system records your IP Address and useragent data.  This information may be processed overseas and your
data will remain confidential.
It is important to note that this information is provided by your internet browser to any website 
you visit; it is not possible to confirm any specific person's identity with this information. We only
use this to prevent abuse of Wikipedia and UTRS and to ensure the proper functionality of UTRS.</p>

<p>We also require your email address so that we can notify you about important information relevant to you.</p>

<p>By creating an account at UTRS, you agree to provide this information and allow UTRS Developers and WMF Labs System Administrators 
to view it for the explicit purpose of maintaining the integrity and operation of the tool.</p>",
					"pt" => ""
			),
			"DataStoreTitle" => array (
					"en" => "Warning: Do not use the Labs Project (this site) if you do not agree to the following: information shared with the Labs Project, including usernames and passwords, will be made available to volunteer administrators and may not be treated confidentially.",
					"pt" => ""
			),
			"DataStore" => array(
					"en" => "<p>We store this data in a secure database, which is visible only to UTRS developers, all of whom
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
removal process is automated, so you don't need to worry about anyone forgetting to hit the \"delete\" button.</p>
<p>Finally, we are not the Wikimedia Foundation.  We are a group of volunteers who have made our identities
known to the foundation.</p>",
					"pt" => ""
			),
			"UserRightsTitle" => array(
					"en" => "What are your rights with regard to this information?",
					"pt" => ""
			),
			"UserRights" => array(
					"en" => "<p>If you wish to see what information has been collected on you by this system, you may email the 
development team at <a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20information%20request\">
utrs-developers@googlegroups.com</a> to request all information associated with your account.</p>

<p>If you do not wish for this information to be collected by UTRS, do not create an account or accept the active terms of use.</p>

<p>If you have already registered at UTRS and wish for your information to be deleted 
immediately, please email the development team at 
<a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20appeal%20removal%20request\">utrs-developers@googlegroups.com</a> 
to have your account deleted from the database.</p>

<p>If you have any questions about this policy, please contact the development team at 
<a href=\"mailto:utrs-developers@googlegroups.com?subject=Privacy%20questions\">utrs-developers@googlegroups.com</a>.</p>",
					"pt" => ""
			),
			"ResponsibilityTitle" => array(
					"en" => "What is your responsibility with the information provided by the interface?",
					"pt" => ""
			),
			"Responsibility" => array(
					"en" => "<p>All information provided from the tool is to remain confidential and not to be shared outside the interface unless:
<ul>
<li>A WMF Staff member is using the data in a way consistent with the <a href="privacy.php">UTRS Privacy Policy</a> and the WMF Privacy Policy.</li>
<li>A CheckUser is storing data for an abusive account on CheckUser Wiki or similiar private medium.</li>
<li>A developer using it to diagnose the tool.</li>
<li>A developer assisting one of the above functions.</li>
</ul>",
					"pt" => ""
			)
	);
}
