<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$eppnRealm = $uregconf->getString('user.realm');
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);

/* Get a reference to our authentication source. */
$asId = $uregconf->getString('auth');
$as = new SimpleSAML_Auth_Simple($asId);

/* Require the usr to be authentcated. */
$as->requireAuth();

/* Retrieve attributes of the user. */
$currentAttributes = $as->getAttributes();

$formFields = $uregconf->getArray('formFields');
$attributes = $uregconf->getArray('attributes');

$showFields = sspmod_userregistration_Util::getFieldsFor('edit_user');
$readOnlyFields = sspmod_userregistration_Util::getReadOnlyFieldsFor('edit_user');

$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'reviewUser.php');
$formGen->fieldsToShow($showFields);
$formGen->setReadOnly($readOnlyFields);

$html = new SimpleSAML_XHTML_Template(
	$config,
	'userregistration:reviewuser.tpl.php',
	'userregistration:userregistration'
);
$html->data['customNavigation'] = $customNavigation;

if(array_key_exists('sender', $_POST)) {
	try{
		// Update user object
		$validator = new sspmod_userregistration_Registration_Validation(
			$formFields,
			$showFields,
			'edit_user');
		$validValues = $validator->validateInput();

		$eppnRealm = $uregconf->getString('user.realm');

		$userInfo = sspmod_userregistration_Util::processInput(
			$validValues,
			$showFields,
			$attributes
		);

		// Always prevent changes on User identification param in DataSource and Session.
		unset($userInfo[$store->userIdAttr]);


		$store->updateUser($currentAttributes[$store->userIdAttr][0], $userInfo);

		// I must override the values with the userInfo values due in processInput i can change the values.
		// But now atributes from the logged user is obsolete, So I can actualize it and get values from session
		// but maybe we could have security problem if IdP isnt configured correctly.

		$session = SimpleSAML_Session::getInstance();
//		$session->setAttribute('givenName', array(0 => 'migivenname'));

		foreach($userInfo as $name => $value) {
			$currentAttributes[$name][0] = $value;
			$session->setAttribute($name, $currentAttributes[$name]);
		}

		$currentAttributes = $as->getAttributes();
		$values = sspmod_userregistration_Util::filterAsAttributes($currentAttributes, $showFields, $attributes);

		header('Location: '.SimpleSAML_Module::getModuleURL('userregistration/reviewUser.php?success'));
		exit();
	} catch(sspmod_userregistration_Error_UserException $e){
		// Some user error detected
		$values = $validator->getRawInput();

		$error = $html->t(
			$e->getMesgId(),
			$e->getTrVars()
		);

		$html->data['error'] = htmlspecialchars($error);
	}
} elseif (array_key_exists('success', $_GET)) {
	$html->data['success'] = True;

	$html->data['logout_url'] = '?logout';
	if (SimpleSAML_Module::isModuleEnabled('sspopenmooc')) {
		$themeconf = SimpleSAML_Configuration::getConfig('module_sspopenmooc.php');
		$urls = $themeconf->getArray('urls');
		if (isset($urls['logout']) && !empty($urls['logout'])) {
			$html->data['logout_url'] = $urls['logout'];
		}
	}
	$html->show();
	exit();	
} elseif (array_key_exists('logout', $_GET)) {
	if ($customNavigation) {
		$as->logout($as->getLoginURL());
	}
	else {
		$as->logout(SimpleSAML_Module::getModuleURL('userregistration/index.php'));
	}
	
	
} else {
	// The GET access this endpoint
	$values = sspmod_userregistration_Util::filterAsAttributes($currentAttributes, $showFields, $attributes);
}
$formGen->setValues($values);
$formGen->setSubmitter('submit_change');
$formHtml = $formGen->genFormHtml();
$html->data['formHtml'] = $formHtml;
$html->data['uid'] = $currentAttributes[$store->userIdAttr][0];
$html->show();
