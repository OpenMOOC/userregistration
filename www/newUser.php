<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$mailoptions = $uregconf->getArray('mail');
$attributes = $uregconf->getArray('attributes');
$formFields = $uregconf->getArray('formFields');
$eppnRealm = $uregconf->getString('user.realm');
$tos = $uregconf->getString('tos', '');
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);

$steps = new sspmod_userregistration_XHTML_Steps();


$systemName = array('%SNAME%' => $uregconf->getString('system.name') );
$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

if (array_key_exists('savepw', $_REQUEST)) {
	// Stage 4: Registration completed
	try{
		$steps->setCurrent(4);
		$listValidate = sspmod_userregistration_Util::getFieldsFor('first_password');
		$validator = new sspmod_userregistration_Registration_Validation(
		 $formFields,
		 $listValidate);
		$validValues = $validator->validateInput();

		$userInfo = sspmod_userregistration_Util::processInput($validValues, $listValidate, $attributes);

		// Adding affiliation (student) when a user is registered
		$userInfo['eduPersonAffiliation'] = 'student';

		$newPw = sspmod_userregistration_Util::validatePassword($validValues);
		$validator->validatePolicyPassword($store->passwordPolicy, $userInfo, $newPw);

		if (isset($userInfo['userPassword'])) {
			$userInfo['userPassword'] = $store->encrypt_pass($userInfo['userPassword']);
		}

		$store->updateUser($_POST['email'], $userInfo);

		$html = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step4_complete.tpl.php',
			'userregistration:userregistration');

			$html->data['systemName'] = $systemName;
			$html->data['customNavigation'] = $customNavigation;
			$html->data['stepsHtml'] = $steps->generate();
			$html->show();
	}catch(sspmod_userregistration_Error_UserException $e){
		// Go back one step
		$steps->setCurrent(3);

		$email = $_REQUEST['email'];
		$token = $_REQUEST['token'];

		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

		$viewAttrPW = array ('userPassword' => 'userPassword');
		$showFields = sspmod_userregistration_Util::getFieldsFor('first_password');

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
		$terr->data['customNavigation'] = $customNavigation;
		$terr->data['stepsHtml'] = $steps->generate();
		$terr->show();
	}
} elseif(array_key_exists('email', $_REQUEST) && array_key_exists('token', $_REQUEST) && !array_key_exists('refreshtoken', $_REQUEST)){
	// Stage 3: User access page from url in e-mail
	$steps->setCurrent(3);
	try{
		$token = $_REQUEST['token'];
		$email = filter_input(
			INPUT_GET,
			'email',
			FILTER_VALIDATE_EMAIL);
		if(!$email)
			throw new SimpleSAML_Error_Exception(
				'E-mail parameter in request is lost');

		$tg = new SimpleSAML_Auth_TimeLimitedToken($mailoptions['token.lifetime']);
		$tg->addVerificationData($email);
		if (!$tg->validate_token($token)) {
			throw new sspmod_userregistration_Error_UserException('invalid_token');
		}

		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

		$viewAttrPW = array ('userPassword' => 'userPassword');
		$showFields = sspmod_userregistration_Util::getFieldsFor('first_password');

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
		$html->data['stepsHtml'] = $steps->generate();
		$html->data['formHtml'] = $formHtml;

		if(!empty($store->passwordPolicy)) {
			$html->data['passwordPolicy'] = $store->passwordPolicy;
			$html->data['passwordPolicytpl'] = SimpleSAML_Module::getModuleDir('userregistration').'/templates/password_policy_tpl.php';
			$html->data['passwordField'] = 'pw1';
		}

		$html->data['customNavigation'] = $customNavigation;
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
		$terr->data['customNavigation'] = $customNavigation;
		$terr->data['stepsHtml'] = $steps->generate();
		$terr->show();
	}
} elseif(array_key_exists('refreshtoken', $_POST)){
	// Stage 2 (b): Resend email token
	$steps->setCurrent(2);

	$email = $_POST['email'];

	$tg = new SimpleSAML_Auth_TimeLimitedToken($mailoptions['token.lifetime']);
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

	$tokenExpiration = $mailoptions['token.lifetime'];
	$mail_data = array(
		'email' => $email,
		'tokenLifetime' => $tokenExpiration,
		'registerurl' => $registerurl,
		'systemName' => $systemName,
	);

	sspmod_userregistration_Util::sendEmail(
		$email,
		$mailoptions['subject'],
		'userregistration:mail1_token.tpl.php',
		$mail_data
	);


	$html = new SimpleSAML_XHTML_Template(
		$config,
		'userregistration:step2_sent.tpl.php',
		'userregistration:userregistration');
	$html->data['email'] = $email;
	$html->data['systemName'] = $systemName;
	$html->data['stepsHtml'] = $steps->generate();
	$html->data['customNavigation'] = $customNavigation;
	$html->show();

} elseif(array_key_exists('sender', $_POST)){
	try{
		// Stage 2: send email token
		$steps->setCurrent(2);

		// Add user object
		$listValidate = sspmod_userregistration_Util::getFieldsFor('new_user');

		$validator = new sspmod_userregistration_Registration_Validation(
		 $formFields,
		 $listValidate);
		$validValues = $validator->validateInput();

		$userInfo = sspmod_userregistration_Util::processInput($validValues, $listValidate, $attributes);

		if(!empty($tos) && !array_key_exists('tos', $_POST)) {
			$e = new sspmod_userregistration_Error_UserException('tos_failed');
			throw $e;
		}		

		$store->addUser($userInfo);

		$email = $userInfo[$store->userRegisterEmailAttr];

		$tg = new SimpleSAML_Auth_TimeLimitedToken($mailoptions['token.lifetime']);
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

		$tokenExpiration = $mailoptions['token.lifetime'];
		$mail_data = array(
			'email' => $email,
			'tokenLifetime' => $tokenExpiration,
			'registerurl' => $registerurl,
			'systemName' => $systemName,
		);

		sspmod_userregistration_Util::sendEmail(
			$email,
			$mailoptions['subject'],
			'userregistration:mail1_token.tpl.php',
			$mail_data
		);

		$html = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step2_sent.tpl.php',
			'userregistration:userregistration');
		$html->data['stepsHtml'] = $steps->generate();
		$html->data['email'] = $email;
		$html->data['systemName'] = $systemName;
		$html->data['customNavigation'] = $customNavigation;
		$html->show();


	}catch(sspmod_userregistration_Error_UserException $e){
		// Some user error detected
		// One step back
		$steps->setCurrent(1);
		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

		$showFields = sspmod_userregistration_Util::getFieldsFor('new_user');
		$formGen->fieldsToShow($showFields);

		$values = $validator->getRawInput();

		$formGen->setValues($values);
		$formGen->setSubmitter('register');

		if (!empty($tos)) {
			$formGen->addTOS($tos);
		}

		$formHtml = $formGen->genFormHtml();

		$terr = new SimpleSAML_XHTML_Template(
		 $config,
		 'userregistration:step1_register.tpl.php',
		 'userregistration:userregistration');
		$terr->data['stepsHtml'] = $steps->generate();
		$terr->data['formHtml'] = $formHtml;

        if ($e->getMesgId() == 'uid_taken_but_not_verified') {
            $email = $userInfo[$store->userRegisterEmailAttr];
			$terr->data['refreshtoken'] = true;
			$terr->data['email'] = $email;
		} elseif ($e->getMesgId() == 'uid_taken') {
			$terr->data['url_lostpassword'] = SimpleSAML_Module::getModuleURL('userregistration/lostPassword.php');
		}

		$error = $terr->t(
			 $e->getMesgId(),
			 $e->getTrVars()
		);

		$terr->data['systemName'] = $systemName;

		$terr->data['error'] = htmlspecialchars($error);
		$terr->data['customNavigation'] = $customNavigation;
		$terr->show();
	}
} else {
	// Stage 1: New user clean access

	$steps->setCurrent(1);

	$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

	$showFields = sspmod_userregistration_Util::getFieldsFor('new_user');

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

	$html->data['stepsHtml'] = $steps->generate();
	$html->data['formHtml'] = $formHtml;

	$html->data['systemName'] = $systemName;
	$html->data['customNavigation'] = $customNavigation;
	$html->show();

}

?>
