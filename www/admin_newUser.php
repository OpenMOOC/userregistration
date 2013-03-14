<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$attributes = $uregconf->getArray('attributes');
$formFields = $uregconf->getArray('formFields');
$eppnRealm = $uregconf->getString('user.realm');
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);

/* Get a reference to our authentication source. */
$asId = $uregconf->getString('admin.auth');
$as = new SimpleSAML_Auth_Simple($asId);
$as->requireAuth();

$systemName = array('%SNAME%' => $uregconf->getString('system.name') );
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

		// Adding affiliation (student) when a user is registered
		$userInfo['eduPersonAffiliation'] = 'student';

		$store->addUser($userInfo);

		$html = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:admin_create_complete.tpl.php',
			'userregistration:userregistration');
		$html->data['systemName'] = $systemName;
		$html->data['customNavigation'] = $customNavigation;
		$html->show();


	} catch(sspmod_userregistration_Error_UserException $e) {
		// Some user error detected
		$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'admin_newUser.php');

		$showFields = sspmod_userregistration_Util::getFieldsFor('admin_new_user');
		$formGen->fieldsToShow($showFields);

		$values = $validator->getRawInput();

		$formGen->setValues($values);
		$formGen->setSubmitter('register');

		$formHtml = $formGen->genFormHtml();

		$terr = new SimpleSAML_XHTML_Template(
		 $config,
		 'userregistration:step1_register.tpl.php',
		 'userregistration:userregistration');
		$terr->data['formHtml'] = $formHtml;

		$error = $terr->t(
			 $e->getMesgId(),
			 $e->getTrVars()
		);

		$terr->data['systemName'] = $systemName;

		$terr->data['admin'] = true;
		$terr->data['error'] = htmlspecialchars($error);
		$terr->data['customNavigation'] = $customNavigation;
		$terr->show();
	}
} else {
	// Stage 1: New user clean access
	$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'admin_newUser.php');

	$showFields = sspmod_userregistration_Util::getFieldsFor('admin_new_user');

	$formGen->fieldsToShow($showFields);

	$formGen->setSubmitter('register');
	$formHtml = $formGen->genFormHtml();

	$html = new SimpleSAML_XHTML_Template(
		$config,
		'userregistration:step1_register.tpl.php',
		'userregistration:userregistration');

	$html->data['formHtml'] = $formHtml;

	$html->data['systemName'] = $systemName;
	$html->data['customNavigation'] = $customNavigation;
	$html->data['admin'] = true;
	$html->show();

}
