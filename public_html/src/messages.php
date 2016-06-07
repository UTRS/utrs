<?php
class SystemMessages {
	public static $system = array (
			// Email syntax is very specific, be careful with editing these. I've reverted some of the changes for technical reasons
			"EmailFrom" => array (
					"en" => "From: Unblock Review Team <noreply-unblock@utrs.wmflabs.org>\r\n",
					//Unblock Review Team can be translated here. Could we do that?
					"pt" => "De: Equipe de revisão de desbloqueios <noreply-unblock@utrs.wmflabs.org>\r\n" 
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
			)
			,
			"RevealEmail" => array(
					"en" => "Revealed this appeals email: ",
					"pt" => "Revelado p email destes pedidos"
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
                    "pt" => ""
            ),
            "NoAPILogin" => array(
                    "en" => "API login not yet implemented. The api is only available for logged in users for now.",
                    "pt" => "Login via API ainda não implementado. O PIA apenas está disponível para usuários registrados por enquanto."
            ),
            "BadParamAPI" => array(
                    "en" => "You have tried to call the UTRS api with bad parameters",
                    "pt" => "Você tentou acionar o API da UTRS com parâmetros incorretos"
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
					"en" => "With the development of UTRS, this project occasionally requires a modified terms of service than when you initially registered.  To continue to participate in this".
							"system, for which your time is greatly appreciated, we require you to first accept these new terms.",
					"pt" => "Com o desenvolvimento do UTRS, este projeto ocasionalmente requer um termo de serviço diferente daquele de quando você se registrou. Para continuar a participar desse".
					                "sistema, pelo qual seu tempo é bastante apreciado, nós solicitamos que aceite antes esses novos termos."
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
					"en" => "<p>Volunteers may have full access to the systems hosting the projects, allowing them access to any data or other information you submit.".
							"<p>As a result, use of your real Wikimedia credentials is highly discouraged in wmflabs.org projects. You should use a different password for your account than you would on projects like Wikipedia, Commons, etc.".
							"<p>By creating an account in this project and/or using other Wikimedia Labs Services, you agree that the volunteer administrators of this project will have access to any data you submit.".
							"<p>Since access to this information by volunteers is fundamental to the operation of Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.",
					"pt" => "<p>Voluntários podem ter acesso total ao sistema que controla os projetos, permitindo a eles acesso a qualquer dado ou outra informação que você fornecer.".
							"<p>Como resultado, o uso de suas reais credenciais da Wikimedia é altamente desencorajado nos projetos do wmflabs.org. Você deve usar uma senha diferente da senha que usa em projetos como Wikipédia, Commons, etc.".
							"<p>Ao criar uma conta neste projeto e/ou usar outros serviços do Wikimedia Labs, você concorda que os administradores voluntários deste projeto tenham acesso a qualquer dado que fornecer.".
							"<p>Já que o acesso a essa informação por voluntários é fundamental para a operação do Labs, estes termos de uso de seus dados se sobrepõem expressamente à Política de Privacidade da Wikimedia Foundation, já que se relaciona ao uso e acesso à sua informação pessoal."
			),
			"ToSAgree" => array(
					"en" => "If you agree check here and click submit:",
					"pt" => "Se concorda, marque aqui e clique em "submeter":"
			),
			"IAccept" => array(
					"en" => "I accept",
					"pt" => "Eu aceito"
			),
			"Clarity" => array(
					//EN version will never actually be shown
					"en" => "For clarity, if there are differences between the English version and the {Your language} version, the English version will be used.",
					"pt" => "Para mais clareza, se houver diferenças entre a versão em inglês e a versão em português, a versão em inglês será usada."
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
					"pt" => ""
			),
			"WikimediaLabsDisclaimerTitle" => array (
					"en" => "Wikimedia Labs Disclaimer",
					"pt" => ""
			),
			"WhatCollectTitle" => array (
					"en" => "What data do we collect, and why?",
					"pt" => ""
			),
			"DataStoreTitle" => array (
					"en" => "How is this data stored, and who can see it?",
					"pt" => ""
			),
			"UserRightsTitle" => array(
					"en" => "What are your rights with regard to this information?",
					"pt" => ""
			),
			"ResponsibilityTitle" => array(
					"en" => "What is your responsibility with the information provided by the interface?",
					"pt" => ""
			),
			"WikimediaLabsDisclaimer" => array (
					"en" => "<p>By using this project, you agree that any private information you give to this project may be made publicly available and not be treated as confidential.".
			
					"<p>By using this project, you agree that the volunteer administrators of this project will have access to any data you submit. This can include your IP address, your username/password combination for accounts created in Labs services, and any other information that you send. The volunteer administrators of this project are bound by the Wikimedia Labs Terms of Use, and are not allowed to share this information or use it in any non-approved way.".
			
					"<p>Since access to this information is fundamental to the operation of Wikimedia Labs, these terms regarding use of your data expressly override the Wikimedia Foundation's Privacy Policy as it relates to the use and access of your personal information.",
					"pt" => ""
			)
	);
	public static $privpol_admin = array (
			"StepsForPrivacy" => array (
					"en" => "Welcome to the Unblock Ticket Request System. We recognize that you are a volunteer administrator working to contribute".
							"towards the world's largest free online encyclopedia. As such, we recognize that there may be some information".
							"you'd rather keep private. We value that privacy and wish to assure you that we have taken steps to ensure".
							"that by responding to unblock requests here, you are not at risk of exposing your identity on the internet. At the".
							"same time, however, in order to ensure proper access, functionality and utilization of UTRS, we do need to collect certain".
							"information that will assist us with those tasks.",
					"pt" => ""
			),
			"WhatCollect" => array (
					"en" => "<p>This system records your IP Address and useragent data.  This information may be processed overseas and your".
							"data will remain confidential.".
							"It is important to note that this information is provided by your internet browser to any website ".
							"you visit; it is not possible to confirm any specific person's identity with this information. We only".
							"use this to prevent abuse of Wikipedia and UTRS and to ensure the proper functionality of UTRS.</p>".

							"<p>We also require your email address so that we can notify you about important information relevant to you.</p>".

							"<p>By creating an account at UTRS, you agree to provide this information and allow UTRS Developers and WMF Labs System Administrators". 
							"to view it for the explicit purpose of maintaining the integrity and operation of the tool.</p>",
					"pt" => ""
			),
			"DataStore" => array(
					"en" => "<p>We store this data in a secure database, which is visible only to UTRS developers, all of whom".
							"are identified to the Wikimedia Foundation, just as Checkusers and Oversighters are required to do. In order to assist ".
							"with reviewing your block, this information is provided to UTRS volunteers as follows:</p>".
							"<ul>".
							"<li>Your email address will be only to you and UTRS Developers and WMF Labs System Administrator</li>".
							"<li>Your useragent and IP address will only be visible to UTRS developers and WMF Labs System Administrators.</li>".
							"<li>Your useragent and IP address addtionally be visible upon request from an approved member of the Wikimedia Foundation.".
							"The use of this is rare, logged and monitered. You agree that any data released under this is subject to the Privacy Policy ".
							"of the Wikimedia Foundation, and this privacy policy is nullified for the released data only.</li>".
							"</ul>".
							"<p>At no point will your data be provided to a third party for any purpose; furthermore, any private and sensitive data".
							"regarding your account is removed after three months of it being in the tool. This ".
							"removal process is automated, so you don't need to worry about anyone forgetting to hit the \"delete\" button.</p>".
							"<p>Finally, we are not the Wikimedia Foundation.  We are a group of volunteers who have made our identities".
							"known to the foundation.</p>",
					"pt" => ""
			),
			"UserRights" => array(
					"en" => "<p>If you wish to see what information has been collected on you by this system, you may email the". 
							"development team at <a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20information%20request\">".
							"utrs-developers@googlegroups.com</a> to request all information associated with your account.</p>".

							"<p>If you do not wish for this information to be collected by UTRS, do not create an account or accept the active terms of use.</p>".

							"<p>If you have already registered at UTRS and wish for your information to be deleted ".
							"immediately, please email the development team at ".
							"<a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20appeal%20removal%20request\">utrs-developers@googlegroups.com</a>". 
							"to have your account deleted from the database.</p>".

							"<p>If you have any questions about this policy, please contact the development team at". 
							"<a href=\"mailto:utrs-developers@googlegroups.com?subject=Privacy%20questions\">utrs-developers@googlegroups.com</a>.</p>",
					"pt" => ""
			),
			"Responsibility" => array(
					"en" => "<p>All information provided from the tool is to remain confidential and not to be shared outside the interface unless:".
							"<ul>".
							"<li>A WMF Staff member is using the data in a way consistent with the <a href=\"privacy.php\">UTRS Privacy Policy</a> and the WMF Privacy Policy.</li>".
							"<li>A CheckUser is storing data for an abusive account on CheckUser Wiki or similiar private medium.</li>".
							"<li>A developer using it to diagnose the tool.</li>".
							"<li>A developer assisting one of the above functions.</li>".
							"</ul>",
					"pt" => ""
			)
	);
	public static $privpol_user = array (
			"StepsForPrivacy" => array (
					"en" => "Welcome to the Unblock Ticket Request System. We recognize that you are a volunteer working to contribute".
							"towards the world's largest free online encyclopedia. As such, we recognize that there may be some information".
							"you'd rather keep private. We value that privacy and wish to assure you that we have taken steps to ensure".
							"that by requesting unblocking here, you are not at risk of exposing your identity on the internet. At the".
							"same time, however, in order to properly process your unblock request, we do need to collect certain".
							"information that will assist us with allow us to distinguish you from others editing from the same or a nearby location.",
					"pt" => ""
			),
			"WhatCollect" => array (
					"en" => "<p>This system records your IP Address and useragent data.  This information may be processed overseas and your".
							"data will remain confidential.".
							"It is important to note that this information is provided by your internet browser to any website ".
							"you visit; it is not possible to confirm any specific person's identity with this information. We only".
							"use this to prevent abuse of Wikipedia and UTRS.</p>".

							"<p>We also require your email address so that we can respond to you with questions and the result of your appeal.</p>".

							"<p>By submitting an appeal at UTRS, you agree to provide this information and allow UTRS volunteers". 
							"to view it for the explicit purpose of reviewing your block on the English Wikipedia.</p>",
					"pt" => ""
			),
			"DataStore" => array(
					"en" => "<p>We store this data in a secure database, which is visible only to UTRS developers, all of whom".
							"are identified to the Wikimedia Foundation, just as Checkusers and Oversighters are required to do. In order to assist ".
							"with reviewing your block, this information is provided to UTRS volunteers as follows:</p>".
							"<ul>".
							"<li>Your email address will be obscured for most people reviewing your block. All emails we send you will be sent through the system,". 
							"so there is no need for volunteers to view your email address in full. As a result, only the domain of your address will be visible to". 
							"most volunteers (for example, UTRSUser@gmail.com will display only as *****@gmail.com). The domain remains visible to assist volunteers". 
							"in determining if you are a legitimate user of a school or business network, if you are editing from one. UTRS tool developers and WMF". 
							"Staff will be able to see your full email address to ensure the tool is working properly or in emergency situations consistent with the". 
							"Wikimedia Foundation Privacy Policy.</li>".
							"<li>Your useragent and IP address will only be visible to UTRS developers and those reviewing the block that have access to the CheckUser". 
							"tool on Wikipedia. CheckUsers need to see your useragent to help differentiate you from the person the block is intended for, and UTRS". 
							"developers need to see this information to ensure the tool is working properly.</li>".
							"<li>In very limited cases your IP address and useragent may be stored to prevent additional abuse of the tool or Wikipedia.</li>".
							"<li>Your useragent and IP address addtionally be visible upon request from an approved member of the Wikimedia Foundation.".
							"<li>The Username (or IP address if you don't have an account), Appeal number, date of appeal, and status of appeal are publically visible on". 
							"the English Wikipedia only if your appeal is active. After that point the information is removed from the English Wikipedia.</li>".
							"The use of this is rare, logged and monitered. You agree that any data released under this is subject to the Privacy Policy ".
							"of the Wikimedia Foundation, and this privacy policy is nullified for the released data only.</li>".
							"</ul>".
							"<p>At no point will your data be provided to a third party for any purpose; furthermore, this information will be removed from our system no ".
							"more than one week after your appeal is closed. This ".
							"removal process is automated, so you don't need to worry about anyone forgetting to hit the \"delete\" button.</p>".
							"<p>Finally, we are not the Wikimedia Foundation.  We are a group of volunteers who have made our identities".
							"known to the foundation.</p>",
					"pt" => ""
			),
			"UserRights" => array(
					"en" => "<p>If you wish to see what information has been collected on you by this system, you may email the". 
							"development team at <a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20information%20request\">".
							"utrs-developers@googlegroups.com</a> to request all information associated with your appeal.</p>".

							"<p>If you do not wish for this information to be collected by UTRS, you may appeal via your talk page on Wikipedia.". 
							"Please note that if you appeal on Wikipedia, your IP address and useragent may be examined by any CheckUser, with cause,". 
							"in accordance with the Wikimedia Foundation Privacy Policy.</p>".

							"<p>If you have already entered an appeal at UTRS and wish for your information to be deleted ".
							"immediately, please email the development team at ".
							"<a href=\"mailto:utrs-developers@googlegroups.com?subject=UTRS%20appeal%20removal%20request\">utrs-developers@googlegroups.com</a>". 
							"to have your appeal deleted from the database. You will then need to appeal your block through one of the alternate venues mentioned". 
							" above. Again, please note that this information will be automatically removed one week after your appeal is resolved.</p>".

							"<p>If you have any questions about this policy, please contact the development team at". 
							"<a href=\"mailto:utrs-developers@googlegroups.com?subject=Privacy%20questions\">utrs-developers@googlegroups.com</a>.</p>",
					"pt" => ""
			)
	);
}
