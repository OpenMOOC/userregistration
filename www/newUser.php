<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$tokenLifetime = $uregconf->getInteger('mailtoken.lifetime');
$viewAttr = $uregconf->getArray('attributes');
$formFields = $uregconf->getArray('formFields');
$eppnRealm = $uregconf->getString('user.realm');
$tos = $uregconf->getString('tos', '');


$systemName = array('%SNAME%' => $uregconf->getString('system.name') );
$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

if (array_key_exists('savepw', $_REQUEST)) {
	try{
		$viewAttrPW = array ('userPassword' => 'userPassword');
		$listValidate = sspmod_userregistration_Util::genFieldView($viewAttrPW);
		$validator = new sspmod_userregistration_Registration_Validation(
		 $formFields,
		 $listValidate);
		$validValues = $validator->validateInput();

		$userInfo = sspmod_userregistration_Util::processInput($validValues, $viewAttrPW);

		$newPw = sspmod_userregistration_Util::validatePassword($validValues);
		$validator->validatePolicyPassword($store->passwordPolicy, $userInfo, $newPw);

		$store->updateUser($_POST['email'], $userInfo);

		$html = new SimpleSAML_XHTML_Template(
		 $config,
		 'userregistration:step4_complete.tpl.php',
		 'userregistration:userregistration');

		$html->data['systemName'] = $systemName;
		$html->show();
	}catch(sspmod_userregistration_Error_UserException $e){
		$email = $_REQUEST['email'];
		$token = $_REQUEST['token'];

		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

		$viewAttrPW = array ('userPassword' => 'userPassword');
		$showFields = sspmod_userregistration_Util::genFieldView($viewAttrPW);

		$formGen->fieldsToShow($showFields);

		$hidden = array(
			'email' => $email,
			'token' => $token,
			'savepw' => true);
		$formGen->addHiddenData($hidden);

		$formGen->setValues(
			array(
				$store->userRegisterEmailAttr => $email
			)
		);

		$formGen->setSubmitter('save');
		$formHtml = $formGen->genFormHtml();

		$terr = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step3_password.tpl.php',
			'userregistration:userregistration');
		$terr->data['formHtml'] = $formHtml;

		if(!empty($store->passwordPolicy)) {
			$terr->data['passwordPolicy'] = $store->passwordPolicy;
			$terr->data['passwordPolicytpl'] = SimpleSAML_Module::getModuleDir('userregistration').'/templates/password_policy_tpl.php';
			$terr->data['passwordField'] = 'pw1';
		}

		$error = $terr->t(
			$e->getMesgId(),
			$e->getTrVars()
		);
		$terr->data['error'] = htmlspecialchars($error);

		$terr->data['systemName'] = $systemName;
		$terr->show();
	}
} elseif(array_key_exists('refreshtoken', $_POST)){
	// Resend token

	$email = $_POST['email'];

	$tg = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
	$tg->addVerificationData($email);
	$newToken = $tg->generate_token();

	$url = SimpleSAML_Utilities::selfURL();

	$registerurl = SimpleSAML_Utilities::addURLparameter(
		$url,
		array(
			'email' => $email,
			'token' => $newToken
		)
	);

	$mailt = new SimpleSAML_XHTML_Template(
		$config,
		'userregistration:mail1_token.tpl.php',
		'userregistration:userregistration');
	$mailt->data['email'] = $email;
	$tokenExpiration = 
	$mailt->data['tokenLifetime'] = $tokenLifetime;
	$mailt->data['registerurl'] = $registerurl;
	$mailt->data['systemName'] = $systemName;

	$mailer = new sspmod_userregistration_XHTML_Mailer(
		$email,
		$uregconf->getString('mail.subject'),
		$uregconf->getString('mail.from'),
		NULL,
		$uregconf->getString('mail.replyto'));
	$mailer->setTemplate($mailt);
	$mailer->send();

	$html = new SimpleSAML_XHTML_Template(
		$config,
		'userregistration:step2_sent.tpl.php',
		'userregistration:userregistration');
	$html->data['systemName'] = $systemName;
	$html->show();

}
else if(array_key_exists('email', $_REQUEST) && array_key_exists('token', $_REQUEST)){
	// Stage 3: User access page from url in e-mail
	try{
		$token = $_REQUEST['token'];
		$email = filter_input(
			INPUT_GET,
			'email',
			FILTER_VALIDATE_EMAIL);
		if(!$email)
			throw new SimpleSAML_Error_Exception(
				'E-mail parameter in request is lost');

		$tg = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
		$tg->addVerificationData($email);
		if (!$tg->validate_token($token)) {
			throw new sspmod_userregistration_Error_UserException('invalid_token');
		}

		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

		$viewAttrPW = array ('userPassword' => 'userPassword');
		$showFields = sspmod_userregistration_Util::genFieldView($viewAttrPW);

		$formGen->fieldsToShow($showFields);

		$hidden = array(
			'email' => $email,
			'token' => $token,
			'savepw' => true);
		$formGen->addHiddenData($hidden);

		$formGen->setValues(
			array(
				$store->userRegisterEmailAttr => $email
			)
		);

		$formGen->setSubmitter('register');
		$formHtml = $formGen->genFormHtml();

		$html = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step3_password.tpl.php',
			'userregistration:userregistration');
		$html->data['formHtml'] = $formHtml;

		if(!empty($store->passwordPolicy)) {
			$html->data['passwordPolicy'] = $store->passwordPolicy;
			$html->data['passwordPolicytpl'] = SimpleSAML_Module::getModuleDir('userregistration').'/templates/password_policy_tpl.php';
			$html->data['passwordField'] = 'pw1';
		}
		$html->show();
	}catch (sspmod_userregistration_Error_UserException $e){

		// Invalid token

		$terr = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step3_password.tpl.php',
			'userregistration:userregistration');

		$error = $terr->t(
			$e->getMesgId(),
			$e->getTrVars()
		);

		if(!empty($store->passwordPolicy)) {
			$terr->data['passwordPolicy'] = $store->passwordPolicy;
			$terr->data['passwordPolicytpl'] = SimpleSAML_Module::getModuleDir('userregistration').'/templates/password_policy_tpl.php';
			$terr->data['passwordField'] = 'pw1';
		}

		$terr->data['error'] = htmlspecialchars($error);

		if ($e->getMesgId() == 'invalid_token') {
			$terr->data['refreshtoken'] = true;
			$terr->data['email'] = $email;
		}
		
		$terr->data['systemName'] = $systemName;
		$terr->show();
	}
} elseif(array_key_exists('sender', $_POST)){
	try{
		// Add user object
	
		$listValidate = sspmod_userregistration_Util::genFieldView($viewAttr);

		$validator = new sspmod_userregistration_Registration_Validation(
		 $formFields,
		 $listValidate);
		$validValues = $validator->validateInput();

		$userInfo = sspmod_userregistration_Util::processInput($validValues, $viewAttr);

		if(!empty($tos) && !array_key_exists('tos', $_POST)) {
			$e = new sspmod_userregistration_Error_UserException('tos_failed');
			throw $e;
		}		

		$store->addUser($userInfo);

		$email = $userInfo[$store->userRegisterEmailAttr];

		$tg = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
		$tg->addVerificationData($email);
		$newToken = $tg->generate_token();

		$url = SimpleSAML_Utilities::selfURL();

		$registerurl = SimpleSAML_Utilities::addURLparameter(
			$url,
			array(
				'email' => $email,
				'token' => $newToken
			)
		);

		$mailt = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:mail1_token.tpl.php',
			'userregistration:userregistration');
		$mailt->data['email'] = $email;
		$tokenExpiration = 
		$mailt->data['tokenLifetime'] = $tokenLifetime;
		$mailt->data['registerurl'] = $registerurl;
		$mailt->data['systemName'] = $systemName;

		$mailer = new sspmod_userregistration_XHTML_Mailer(
			$email,
			$uregconf->getString('mail.subject'),
			$uregconf->getString('mail.from'),
			NULL,
			$uregconf->getString('mail.replyto'));
		$mailer->setTemplate($mailt);
		$mailer->send();

		$html = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step2_sent.tpl.php',
			'userregistration:userregistration');
		$html->data['systemName'] = $systemName;
		$html->show();


	}catch(sspmod_userregistration_Error_UserException $e){
		// Some user error detected
		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

		$showFields = sspmod_userregistration_Util::genFieldView($viewAttr);
		$formGen->fieldsToShow($showFields);

		$values = $validator->getRawInput();

		$formGen->setValues($values);
		$formGen->setSubmitter('register');

		if (!empty($tos)) {
			$formGen->addTOS($tos);
		}

		$formHtml = $formGen->genFormHtml();

		$html = new SimpleSAML_XHTML_Template(
		 $config,
		 'userregistration:step1_register.tpl.php',
		 'userregistration:userregistration');
		$html->data['formHtml'] = $formHtml;

		$error = $html->t(
			 $e->getMesgId(),
			 $e->getTrVars()
		);

		$html->data['systemName'] = $systemName;
		$html->data['error'] = htmlspecialchars($error);
		$html->show();
	}

} else {
	// Stage 1: New user clean access

	$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

	$showFields = sspmod_userregistration_Util::genFieldView($viewAttr);

	$formGen->fieldsToShow($showFields);

	if (!empty($tos)) {
		$formGen->addTOS($tos);
	}
	$formGen->setSubmitter('register');
	$formHtml = $formGen->genFormHtml();

	$html = new SimpleSAML_XHTML_Template(
		$config,
		'userregistration:step1_register.tpl.php',
		'userregistration:userregistration');
	$html->data['systemName'] = $systemName;


	$html->data['formHtml'] = $formHtml;

	$html->show();

}

?>
