<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$tokenLifetime = $uregconf->getInteger('mailtoken.lifetime');
$formFields = $uregconf->getArray('formFields');
$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);

$systemName = array('%SNAME%' => $uregconf->getString('system.name') );

$mail_param = $uregconf->getString('user.register.email.param','mail');
$uid_param = $uregconf->getString('user.id.param','uid');

/* Get a reference to our authentication source. */
$asId = $uregconf->getString('auth');
$as = new SimpleSAML_Auth_Simple($asId);
$as->requireAuth();
$attributes = $as->getAttributes();

$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'changeMail.php');
$fields = array('newmail');
$formGen->fieldsToShow($fields);

$html = new SimpleSAML_XHTML_Template(
	$config,
	'userregistration:change_mail.tpl.php',
	'userregistration:userregistration');

$html->data['customNavigation'] = $customNavigation;

if(array_key_exists('newmail', $_REQUEST) && array_key_exists('oldmail', $_REQUEST) && array_key_exists('token1', $_REQUEST) && array_key_exists('token2', $_REQUEST)){
	// Stage 3: User access page from url in e-mail
	try{
		$token1 = $_REQUEST['token1'];
        $token2 = $_REQUEST['token2'];
		$newmail = filter_input(
			INPUT_GET,
			'newmail',
			FILTER_VALIDATE_EMAIL);
		$oldmail = filter_input(
			INPUT_GET,
			'oldmail',
			FILTER_VALIDATE_EMAIL);
		if(!$newmail) {
			throw new SimpleSAML_Error_Exception('E-mail parameter in request is lost');
        }
		if(!$oldmail) {
			throw new SimpleSAML_Error_Exception('Old E-mail parameter in request is lost');
        }
        if ($attributes[$mail_param][0] != $oldmail) {
   			throw new SimpleSAML_Error_Exception('The old e-mail parameter did not match the mail of the actual logged user');
        }

		$tg = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
		$tg->addVerificationData($newmail);
		if (!$tg->validate_token($token1)) {
			throw new sspmod_userregistration_Error_UserException('invalid_token');
		}


		$tg2 = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
		$tg2->addVerificationData($oldmail);
		if (!$tg->validate_token($token2)) {
			throw new sspmod_userregistration_Error_UserException('invalid_token');
		}

        // $store->updateUser($_POST['email'], $userInfo);
        // Hay que comprobar si para el uid se utiliza el mail y en ese caso actualizarlo tb
        // hay q ver donde se almacena el oldmail -- irisMailAlternateAddress


	} catch (sspmod_userregistration_Error_UserException $e){

		// Invalid token

		$terr = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step1_ch_mail.tpl.php',
			'userregistration:userregistration');

		$error = $terr->t(
			$e->getMesgId(),
			$e->getTrVars()
		);

		$terr->data['error'] = htmlspecialchars($error);

		if ($e->getMesgId() == 'invalid_token') {
			$terr->data['refreshtoken'] = true;
			$terr->data['newmail'] = $newmail;
		}
		
		$terr->data['systemName'] = $systemName;
		$terr->data['customNavigation'] = $customNavigation;
		$terr->show();
	}
} elseif(array_key_exists('refreshtoken', $_POST)){
	// Resend token

    try {
	    $newmail = $_POST['newmail'];

        $oldmail = $attributes[$mail_param][0];

	    $tg = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
	    $tg->addVerificationData($newmail);
	    $newToken = $tg->generate_token();

	    $tg2 = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
	    $tg2->addVerificationData($oldmail);
	    $oldToken = $tg->generate_token();

	    $url = SimpleSAML_Utilities::selfURL();

	    $registerurl = SimpleSAML_Utilities::addURLparameter(
		    $url,
		    array(
			    'newmail' => $newmail,
			    'oldmail' => $oldmail,
			    'token1' => $newToken,
			    'token2' => $oldToken
		    )
	    );

	    $mailt = new SimpleSAML_XHTML_Template(
		    $config,
		    'userregistration:mail1_ch_token.tpl.php',
		    'userregistration:userregistration');
	    $mailt->data['newmail'] = $newmail;
	    $mailt->data['tokenLifetime'] = $tokenLifetime;
	    $mailt->data['changepwurl'] = $changepwurl;
	    $mailt->data['systemName'] = $systemName;

	    $mailer = new sspmod_userregistration_XHTML_Mailer(
		    $newmail,
		    $uregconf->getString('mail.subject'),
		    $uregconf->getString('mail.from'),
		    NULL,
		    $uregconf->getString('mail.replyto'));
	    $mailer->setTemplate($mailt);
	    $mailer->send();

	    $html = new SimpleSAML_XHTML_Template(
		    $config,
		    'userregistration:step2_ch_sent.tpl.php',
		    'userregistration:userregistration');
	    $html->data['newmail'] = $newmail;
	    $html->data['systemName'] = $systemName;
	    $html->data['customNavigation'] = $customNavigation;
	    $html->show();
        exit();

	} catch(sspmod_userregistration_Error_UserException $e) {
		// Some user error detected
		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'changeMail.php');

		$showFields = sspmod_userregistration_Util::getFieldsFor('change_mail');
		$formGen->fieldsToShow($showFields);

        $validator = new sspmod_userregistration_Registration_Validation(
		         $formFields,
		         $listValidate);

		$values = $validator->getRawInput();

		$formGen->setValues($values);
		$formGen->setSubmitter('save');

		$formHtml = $formGen->genFormHtml();

		$terr = new SimpleSAML_XHTML_Template(
		 $config,
		 'userregistration:step1_ch_email.tpl.php',
		 'userregistration:userregistration');
		$terr->data['formHtml'] = $formHtml;

		$error = $terr->t(
			 $e->getMesgId(),
			 $e->getTrVars()
		);

		$terr->data['systemName'] = $systemName;

		$terr->data['error'] = htmlspecialchars($error);
		$terr->data['customNavigation'] = $customNavigation;
		$terr->show();
	}

} else if (array_key_exists('sender', $_REQUEST) && array_key_exists('newmail', $_REQUEST) && !empty($_REQUEST['newmail'])) {

    try {
		$listValidate = sspmod_userregistration_Util::getFieldsFor('change_mail');

		$validator = new sspmod_userregistration_Registration_Validation(
		 $formFields,
		 $listValidate);
		$validValues = $validator->validateInput();

		$userInfo = sspmod_userregistration_Util::processInput($validValues, $listValidate, $attributes);

		$newmail = $userInfo['newmail'];
        
        $oldmail = $attributes[$mail_param][0];

		$tg = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
		$tg->addVerificationData($newmail);
		$newToken = $tg->generate_token();

		$tg2 = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
		$tg2->addVerificationData($oldmail);
		$oldToken = $tg->generate_token();

		$url = SimpleSAML_Utilities::selfURL();

		$changepwurl = SimpleSAML_Utilities::addURLparameter(
			$url,
			array(
				'newmail' => $newmail,
                'oldmail' => $oldmail,
				'token1' => $newToken,
				'token2' => $oldToken
			)
		);

		$mailt = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:mail1_ch_token.tpl.php',
			'userregistration:userregistration');
		$mailt->data['newmail'] = $newmail;
		$mailt->data['tokenLifetime'] = $tokenLifetime;
		$mailt->data['changepwurl'] = $changepwurl;
		$mailt->data['systemName'] = $systemName;

		$mailer = new sspmod_userregistration_XHTML_Mailer(
			$newmail,
			$uregconf->getString('mail.subject'),
			$uregconf->getString('mail.from'),
			NULL,
			$uregconf->getString('mail.replyto'));
		$mailer->setTemplate($mailt);
		$mailer->send();

		$html = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step2_ch_sent.tpl.php',
			'userregistration:userregistration');
		$html->data['newmail'] = $newmail;
		$html->data['systemName'] = $systemName;
		$html->data['customNavigation'] = $customNavigation;
		$html->show();
        exit();

	} catch(sspmod_userregistration_Error_UserException $e) {
		// Some user error detected
		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'changeMail.php');

		$showFields = sspmod_userregistration_Util::getFieldsFor('change_mail');
		$formGen->fieldsToShow($showFields);

        $validator = new sspmod_userregistration_Registration_Validation(
		         $formFields,
		         $listValidate);

		$values = $validator->getRawInput();

		$formGen->setValues($values);
		$formGen->setSubmitter('save');

		$formHtml = $formGen->genFormHtml();

		$terr = new SimpleSAML_XHTML_Template(
		 $config,
		 'userregistration:step1_ch_email.tpl.php',
		 'userregistration:userregistration');
		$terr->data['formHtml'] = $formHtml;

		$error = $terr->t(
			 $e->getMesgId(),
			 $e->getTrVars()
		);

		$terr->data['systemName'] = $systemName;

		$terr->data['error'] = htmlspecialchars($error);
		$terr->data['customNavigation'] = $customNavigation;
		$terr->show();
	}

} elseif (array_key_exists('success', $_GET)) {
	$html->data['userMessage'] = 'message_chpw';
        $html->show();
} elseif(array_key_exists('logout', $_GET)) {
	$as->logout(SimpleSAML_Module::getModuleURL('userregistration/index.php'));
}
else {
    $formGen->setSubmitter('save');
    $html->data['formHtml'] = $formGen->genFormHtml();
    $html->data['uid'] = $attributes[$store->userIdAttr][0];
    $html->show();
}
