<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$attributes = $uregconf->getArray('attributes');
$formFields = $uregconf->getArray('formFields');
$mailoptions = $uregconf->getArray('mail');
$eppnRealm = $uregconf->getString('user.realm');
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);

/* Get a reference to our authentication source. */
$asId = $uregconf->getString('admin.auth');
$as = new SimpleSAML_Auth_Simple($asId);
$as->requireAuth();

$systemName = $uregconf->getString('system.name');
$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

if (array_key_exists('sender', $_POST)) {
	try {
		// Add user object
		$listValidate = sspmod_userregistration_Util::getFieldsFor('admin_new_user');

		$validator = new sspmod_userregistration_Registration_Validation(
		 $formFields,
		 $listValidate);
		$validValues = $validator->validateInput();

		$userInfo = sspmod_userregistration_Util::processInput($validValues, $listValidate, $attributes);
		$userInfo['userPassword'] = sspmod_userregistration_Util::validatePassword($validValues);
		$validator->validatePolicyPassword($store->passwordPolicy, $userInfo, $userInfo['userPassword']);

		$store->addUser($userInfo);


		$html = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:admin_create_complete.tpl.php',
			'userregistration:userregistration');
		$html->data['customNavigation'] = $customNavigation;
        $html->data['systemName'] = $systemName;
		$html->data['userid'] = $userInfo[$store->userIdAttr];
		
		// Send user details to his/her email address
		if (isset($_POST['sendemail'])) {
			$email = $userInfo[$store->userRegisterEmailAttr];
			$subject = $mailoptions['admin_create_subject'];

			// Additional translations. Use dummy template
			$trans = new SimpleSAML_XHTML_Template(
				$config,
				'userregistration:mail_admin_created_account.tpl.php',
				'login'
			);
			$data = array(
				'userid_translated' => $trans->t('username'),
				'userid' => $userInfo[$store->userIdAttr],
				'password_translated' => $trans->t('password'),
				'password' => $userInfo['userPassword'],
				'systemName' => $systemName,
			);

			sspmod_userregistration_Util::sendEmail(
				$email,
				$subject,
				'userregistration:mail_admin_created_account.tpl.php',
				$data
			);

			$html->data['email'] = $email;
			$html->data['mail_sent'] = true;
		}

		$html->show();


	} catch(sspmod_userregistration_Error_UserException $e) {
		// Some user error detected
		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'admin_newUser.php');

		$showFields = sspmod_userregistration_Util::getFieldsFor('admin_new_user');
		$readOnlyFields = sspmod_userregistration_Util::getReadOnlyFieldsFor('admin_new_user');
		$formGen->fieldsToShow($showFields);
		$formGen->setReadOnly($readOnlyFields);

		$values = $validator->getRawInput();

		$formGen->addSendEmail(true);
		$formGen->addGeneratePassword();
		$formGen->setValues($values);
		$formGen->setSubmitter('register');

		$formHtml = $formGen->genFormHtml();

		$terr = new SimpleSAML_XHTML_Template(
		 $config,
		 'userregistration:admin_create_user.tpl.php',
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

		$terr->data['systemName'] = array('%SNAME%' => $systemName);

		$terr->data['admin'] = true;
		$terr->data['error'] = htmlspecialchars($error);
		$terr->data['customNavigation'] = $customNavigation;
		$terr->show();
	}
} else {
	// Stage 1: New user clean access
	$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'admin_newUser.php');

	$showFields = sspmod_userregistration_Util::getFieldsFor('admin_new_user');
	$readOnlyFields = sspmod_userregistration_Util::getReadOnlyFieldsFor('admin_new_user');

	$formGen->fieldsToShow($showFields);
	$formGen->setReadOnly($readOnlyFields);

	$formGen->setSubmitter('register');
	$formGen->addSendEmail(true);
	$formGen->addGeneratePassword();
	$formHtml = $formGen->genFormHtml();

	$html = new SimpleSAML_XHTML_Template(
		$config,
		'userregistration:admin_create_user.tpl.php',
		'userregistration:userregistration');

	$html->data['formHtml'] = $formHtml;

	if(!empty($store->passwordPolicy)) {
		$html->data['passwordPolicy'] = $store->passwordPolicy;
		$html->data['passwordPolicytpl'] = SimpleSAML_Module::getModuleDir('userregistration').'/templates/password_policy_tpl.php';
		$html->data['passwordField'] = 'pw1';
	}

	$html->data['systemName'] = array('%SNAME%' => $systemName);
	$html->data['customNavigation'] = $customNavigation;
	$html->data['admin'] = true;
	$html->show();

}
