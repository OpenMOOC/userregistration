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
$attributes = $as->getAttributes();

$formFields = $uregconf->getArray('formFields');
$reviewAttr = $uregconf->getArray('attributes');

$showFields = array();
$readOnlyFields = array();

foreach ($formFields as $name => $field) {
	if(array_key_exists('show',$field['layout']) && $field['layout']['show']) {
		$showFields[] = $name;
	}
	if(array_key_exists('read_only',$field['layout']) && $field['layout']['read_only']) {
		$readOnlyFields[] = $name;
	}
}

$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'reviewUser.php');
$formGen->fieldsToShow($showFields);
$formGen->setReadOnly($readOnlyFields);

$html = new SimpleSAML_XHTML_Template(
	$config,
	'userregistration:reviewuser.tpl.php',
	'userregistration:userregistration'
);


if(array_key_exists('sender', $_POST)) {
	try{
		// Update user object
		$validator = new sspmod_userregistration_Registration_Validation(
			$formFields,
			$showFields
		);
		$validValues = $validator->validateInput();

		// FIXME: Filter password
		$remove = array('userPassword' => NULL);
		$reviewAttr = array_diff_key($reviewAttr, $remove);

		$eppnRealm = $uregconf->getString('user.realm');

		$userInfo = sspmod_userregistration_Util::processInput(
			$validValues,
			$reviewAttr
		);

		// Always prevent changes on User identification param in DataSource and Session.
		unset($userInfo[$store->userIdAttr]);


		$store->updateUser($attributes[$store->userIdAttr][0], $userInfo);

		// I must override the values with the userInfo values due in processInput i can change the values.
		// But now atributes from the logged user is obsolete, So I can actualize it and get values from session
		// but maybe we could have security problem if IdP isnt configured correctly.

		$session = SimpleSAML_Session::getInstance();
//		$session->setAttribute('givenName', array(0 => 'migivenname'));

		foreach($userInfo as $name => $value) {
			$attributes[$name][0] = $value;
			$session->setAttribute($name, $attributes[$name]);
		}

		$attributes = $as->getAttributes();
		$values = sspmod_userregistration_Util::filterAsAttributes($attributes, $reviewAttr);

		$html->data['userMessage'] = 'message_chuinfo';

	}catch(sspmod_userregistration_Error_UserException $e){
		// Some user error detected
		$values = $validator->getRawInput();

		$values['mail'] = $attributes['mail'][0];

		$error = $html->t(
			$e->getMesgId(),
			$e->getTrVars()
		);

		$html->data['error'] = htmlspecialchars($error);
	}
}elseif(array_key_exists('logout', $_GET)) {
	$as->logout(SimpleSAML_Module::getModuleURL('userregistration/index.php'));
} else {
	// The GET access this endpoint
	$values = sspmod_userregistration_Util::filterAsAttributes($attributes, $reviewAttr);
}

$formGen->setValues($values);
$formGen->setSubmitter('submit_change');
$formHtml = $formGen->genFormHtml();
$html->data['formHtml'] = $formHtml;
$html->data['uid'] = $attributes[$store->userIdAttr][0];
$html->data['customNavigation'] = $customNavigation;
$html->show();

?>
