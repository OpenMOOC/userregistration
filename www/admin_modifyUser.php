<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$eppnRealm = $uregconf->getString('user.realm');
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);
$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

/* Get a reference to our authentication source. */
$asId = $uregconf->getString('admin.auth');
$as = new SimpleSAML_Auth_Simple($asId);
$as->requireAuth();

$systemName = $uregconf->getString('system.name');
$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : '';
$attr = isset($_REQUEST['attr']) ? $_REQUEST['attr'] : '';
$pattern = isset($_REQUEST['pattern']) ? $_REQUEST['pattern'] : '';

if (empty($user)) {
    throw new sspmod_userregistration_Error_UserException(
        'void_value',
        'user',
        '',
        'Missing parameter user'
    );
} elseif (!$store->isRegistered($store->userIdAttr, $user)) {
    throw new sspmod_userregistration_Error_UserException(
        'email_not_found',
        $user,
        '',
        'User ' . $user . ' is not registered!'
    );
}

/* Retrieve attributes of the user. */
$currentAttributes = $store->findAndGetUser($store->userIdAttr, $user);

$formFields = $uregconf->getArray('formFields');
$attributes = $uregconf->getArray('attributes');

$showFields = sspmod_userregistration_Util::getFieldsFor('admin_edit_user');
$readOnlyFields = sspmod_userregistration_Util::getReadOnlyFieldsFor('admin_edit_user');

$formGen = new sspmod_userregistration_XHTML_Form($formFields, 'admin_modifyUser.php');
$formGen->fieldsToShow($showFields);
$formGen->setReadOnly($readOnlyFields);


$html = new SimpleSAML_XHTML_Template(
	$config,
	'userregistration:admin_modify_user.tpl.php',
	'userregistration:userregistration'
);

// Cancel modification, return to search
$cancel_url = SimpleSAML_Utilities::addURLparameter(
	SimpleSAML_Module::getModuleURL('userregistration/admin_manageUsers.php'),
	array(
		'search' => '',
		'attr' => $attr,
		'pattern' => $pattern,
	)
);

$formGen->addSendEmail(true);
$formGen->addGeneratePassword();
$formGen->addCancelButton(
	$html->t('cancel'),
	$cancel_url
);

$html->data['customNavigation'] = $customNavigation;

if(array_key_exists('sender', $_POST)) {
	try{
		// Update user object
		$validator = new sspmod_userregistration_Registration_Validation(
			$formFields,
            $showFields,
            'admin_edit_user'
		);
		$validValues = $validator->validateInput();

		$eppnRealm = $uregconf->getString('user.realm');

		$userInfo = sspmod_userregistration_Util::processInput(
			$validValues,
			$showFields,
			$attributes
		);

		// Optional password field
		$clearPassword = null;
		if (empty($userInfo['userPassword'])) {
			unset($userInfo['userPassword']);
		} else {
			$clearPassword = $userInfo['userPassword'];
			$userInfo['userPassword'] = sspmod_userregistration_Util::validatePassword($validValues);
			if(!empty($store->passwordPolicy)) {
				$validator->validatePolicyPassword($store->passwordPolicy, $attributes, $userInfo['userPassword']);
			}
			$userInfo['userPassword'] = $store->encrypt_pass($userInfo['userPassword']);
		}

		// Always prevent changes on User identification param in DataSource and Session.
		unset($userInfo[$store->userIdAttr]);


		$store->updateUser($currentAttributes[$store->userIdAttr], $userInfo);


		if (isset($_POST['sendemail'])) {
			$email = $currentAttributes[$store->userRegisterEmailAttr];
			$subject = $uregconf->getString('mail.admin_modify_subject');

			// Additional translations. Use dummy template
			$trans = new SimpleSAML_XHTML_Template(
				$config,
				'userregistration:mail_admin_modified_account.tpl.php',
				'login'
			);

			$password = ($clearPassword === null) ?  $html->t('password_not_modified') : $clearPassword;

			$data = array(
				'userid_translated' => $trans->t('username'),
				'userid' => $currentAttributes[$store->userIdAttr],
				'password_translated' => $trans->t('password'),
				'password' => $password,
				'systemName' => $systemName,
			);

			sspmod_userregistration_Util::sendEmail(
				$email,
				$subject,
				'userregistration:mail_admin_modified_account.tpl.php',
				$data
			);
		}

		// I must override the values with the userInfo values due in processInput i can change the values.
		// But now atributes from the logged user is obsolete, So I can actualize it and get values from session
		// but maybe we could have security problem if IdP isnt configured correctly.

		$values = $currentAttributes;

        $url = SimpleSAML_Utilities::addURLparameter(
            SimpleSAML_Module::getModuleURL('userregistration/admin_manageUsers.php'),
            array(
                'search' => '',
                'attr' => $attr,
                'pattern' => $pattern,
            )
        );
		header('Location: '.$url);
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
} else {
	// The GET access this endpoint
	$values = $currentAttributes;
}
$formGen->setValues($values);
$formGen->setSubmitter('submit_change');
$formGen->addHiddenData(
    array(
        'user' => $user,
        'pattern' => $pattern,
        'attr' => $attr,
    )
);

if(!empty($store->passwordPolicy)) {
	$html->data['passwordPolicy'] = $store->passwordPolicy;
	$html->data['passwordPolicytpl'] = SimpleSAML_Module::getModuleDir('userregistration').'/templates/password_policy_tpl.php';
	$html->data['passwordField'] = 'pw1';
	if(array_key_exists('no.contains', $store->passwordPolicy)) {
		$html->data['forbiddenValues'] = array();
		$keys = '';
		foreach($store->passwordPolicy['no.contains'] as $key) {
			if(array_key_exists($key, $attributes) && !empty($attributes[$key])) {
				$value = $attributes[$key];
				if(is_array($value)) {
					$value = $value[0];
				}
				$html->data['forbiddenValues'][] = $value;
				$keys[] = $key;
			}
		}
		$html->data['forbiddenValuesFieldnames'] = '';
		if (!empty($keys)) {
			$html->data['forbiddenValuesFieldnames'] = implode(", ", $keys);
		}
		$html->data['passwordPolicytpl'] = SimpleSAML_Module::getModuleDir('userregistration').'/templates/password_policy_tpl.php';
	}
}


$formHtml = $formGen->genFormHtml();
$html->data['admin'] = true;
$html->data['formHtml'] = $formHtml;
$html->data['uid'] = $currentAttributes[$store->userIdAttr];
$html->show();
