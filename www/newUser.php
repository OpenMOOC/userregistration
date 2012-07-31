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


if(array_key_exists('emailreg', $_REQUEST)){
	// Stage 2: User have submitted e-mail adress for registration
	try {
		$email = filter_input(INPUT_POST, 'emailreg', FILTER_VALIDATE_EMAIL);
		if(!$email){
			$rawValue = isset($_REQUEST['emailreg'])?$_REQUEST['emailreg']:NULL;
			if(!$rawValue){
				throw new sspmod_userregistration_Error_UserException(
					'void_value',
					$store->userRegisterEmailAttr,
					'',
					'Validation of user input failed.'
					.' Field:'.$store->userRegisterEmailAttr
					.' is empty');
			}else{
				throw new sspmod_userregistration_Error_UserException(
					'illegale_value',
					$store->userRegisterEmailAttr,
					$rawValue,
					'Validation of user input failed.'
					.' Field:'.$store->userRegisterEmailAttr
					.' Value:'.$rawValue);
			}
		}

		if($store->isRegistered($store->userRegisterEmailAttr, $email) ) {
			$html = new SimpleSAML_XHTML_Template(
				$config,
				'userregistration:step5_mailUsed.tpl.php',
				'userregistration:userregistration');
			$html->data['systemName'] = $systemName;

			$html->show();
		} else {
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
	}catch(sspmod_userregistration_Error_UserException $e){
		$et = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step1_email.tpl.php',
			'userregistration:userregistration');
		$et->data['email'] = $_POST['emailreg'];
		$et->data['systemName'] = $systemName;

		$error = $et->t(
			$e->getMesgId(),
			$e->getTrVars());
		$et->data['error'] = htmlspecialchars($error);

		$et->show();
	}

}elseif(array_key_exists('token', $_GET)){
	// Stage 3: User access page from url in e-mail
	try{
		$email = filter_input(
			INPUT_GET,
			'email',
			FILTER_VALIDATE_EMAIL);
		if(!$email)
			throw new SimpleSAML_Error_Exception(
				'E-mail parameter in request is lost');

		$tg = new SimpleSAML_Auth_TimeLimitedToken($tokenLifetime);
		$tg->addVerificationData($email);
		$token = $_REQUEST['token'];
		if (!$tg->validate_token($token))
			throw new sspmod_userregistration_Error_UserException('invalid_token');

		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

		$showFields = sspmod_userregistration_Util::genFieldView($viewAttr);
		$formGen->fieldsToShow($showFields);
		$formGen->setReadOnly($store->userRegisterEmailAttr);

		$hidden = array(
			'emailconfirmed' => $email,
			'token' => $token);
		$formGen->addHiddenData($hidden);
		if (!empty($tos)) {
			$formGen->addTOS();
		}
		$formGen->setValues(
			array(
				$store->userRegisterEmailAttr => $email
			)
		);

		$formGen->setSubmitter('submit_change');
		$formHtml = $formGen->genFormHtml();

		$html = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step3_register.tpl.php',
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
			'userregistration:step1_email.tpl.php',
			'userregistration:userregistration');

		$error = $terr->t(
			$e->getMesgId(),
			$e->getTrVars()
		);
		$terr->data['error'] = htmlspecialchars($error);
		$terr->data['systemName'] = $systemName;
		$terr->show();
	}
}elseif(array_key_exists('sender', $_POST)){
	try{
		 // Add or update user object
		 $listValidate = sspmod_userregistration_Util::genFieldView($viewAttr);
		 $validator = new sspmod_userregistration_Registration_Validation(
			 $formFields,
			 $listValidate);
		 $validValues = $validator->validateInput();


		 $userInfo = sspmod_userregistration_Util::processInput($validValues, $viewAttr);


		 $newPw = sspmod_userregistration_Util::validatePassword($validValues);
		 $validator->validatePolicyPassword($store->passwordPolicy, $userInfo, $newPw);

		if(!empty($tos) && !array_key_exists('tos', $_POST)) {
			$e = new sspmod_userregistration_Error_UserException('tos_failed');
			throw $e;
		}		

		 $store->addUser($userInfo);

		 $html = new SimpleSAML_XHTML_Template(
			 $config,
			 'userregistration:step4_complete.tpl.php',
			 'userregistration:userregistration');

		 $html->data['systemName'] = $systemName;
		 $html->show();
	}catch(sspmod_userregistration_Error_UserException $e){
		 // Some user error detected
		 $formGen = new sspmod_userregistration_XHTML_Form($formFields, 'newUser.php');

		 $showFields = sspmod_userregistration_Util::genFieldView($viewAttr);
		 $formGen->fieldsToShow($showFields);
		 $formGen->setReadOnly($store->userRegisterEmailAttr);

		 $values = $validator->getRawInput();

		 $hidden = array();
		 $values[$store->userRegisterEmailAttr] = $hidden['emailconfirmed'] = $_REQUEST['emailconfirmed'];
		 $hidden['token'] = $_REQUEST['token'];
		 $formGen->addHiddenData($hidden);
		 $values['pw1'] = '';
		 $values['pw2'] = '';

		 $formGen->setValues($values);
		 $formGen->setSubmitter('submit_change');

		if (!empty($tos)) {
			$formGen->addTOS();
		}

		 $formHtml = $formGen->genFormHtml();

		 $html = new SimpleSAML_XHTML_Template(
			 $config,
			 'userregistration:step3_register.tpl.php',
			 'userregistration:userregistration');
		 $html->data['formHtml'] = $formHtml;

		$error = $html->t(
			 $e->getMesgId(),
			 $e->getTrVars()
		);

		if(!empty($store->passwordPolicy)) {
			$html->data['passwordPolicy'] = $store->passwordPolicy;
			$html->data['passwordPolicytpl'] = SimpleSAML_Module::getModuleDir('userregistration').'/templates/password_policy_tpl.php';
			$html->data['passwordField'] = 'pw1';
		}

		$html->data['error'] = htmlspecialchars($error);
		$html->show();
	}
} else {

	// Stage 1: New user clean access
	$html = new SimpleSAML_XHTML_Template(
		$config,
		'userregistration:step1_email.tpl.php',
		'userregistration:userregistration');
	$html->data['systemName'] = $systemName;

	$logged_and_same_auth = sspmod_userregistration_Util::checkLoggedAndSameAuth();
	if($logged_and_same_auth) {
		$html->data['logouturl'] = $logged_and_same_auth->getLogoutURL();
	}
	$html->show();
}

?>
