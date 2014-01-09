<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$mailoptions = $uregconf->getArray('mail');
$attributes = $uregconf->getArray('attributes');
$formFields = $uregconf->getArray('formFields');
$known_email_providers = $uregconf->getArray('known.email.providers');
$eppnRealm = $uregconf->getString('user.realm');
$tos = $uregconf->getString('tos', '');
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);
$asId = $uregconf->getString('auth');
$as = new SimpleSAML_Auth_Simple($asId);

$steps = new sspmod_userregistration_XHTML_Steps();


$systemName = array('%SNAME%' => $uregconf->getString('system.name') );
$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

$registration = new sspmod_userregistration_Registration($config);
$registration->setFormFields($formFields);
$registration->setTOS($tos);
$registration->setSystemName($systemName);
$registration->setAttributes($attributes);
$registration->setMailOptions($mailoptions);
$registration->setKnownEmailProviders($known_email_providers);
$registration->setCustomNavigation($customNavigation);
$registration->setAs($as);

if (array_key_exists('savepw', $_REQUEST)) {
	// Stage 4: Registration completed
	$result_step_4 = $registration->step4();

	if (is_a($result_step_4, 'Exception')) {
		$email = $_REQUEST['email'];
		$registration->step3($result_step_4, $email);
	}

} elseif(array_key_exists('token', $_REQUEST) && !array_key_exists('refreshtoken', $_REQUEST)){
	// Stage 3: User access page from url in e-mail
	$result_step_3 = $registration->step3();

	if (is_a($result_step_3, 'Exception')) {
		$registration->step3($result_step_3);
	}

} elseif(array_key_exists('manualtoken', $_REQUEST) && !array_key_exists('refreshtoken', $_REQUEST)){
	// Stage 2 (c): User access page from alternative url in e-mail
	$result_step_2c = $registration->step2c();

	if (is_a($result_step_2c, 'Exception')) {
		$registration->step1($result_step_2c);
	}

} elseif(array_key_exists('refreshtoken', $_POST)){
	// Stage 2 (b): Resend email token
	$registration->step2(TRUE);

} elseif(array_key_exists('sender', $_POST)){
	// Stage 2 (a): Send verification email
	$result_step_2 = $registration->step2();

	if (is_a($result_step_2, 'Exception')) {
		$registration->step1($result_step_2);
	}

} else {
	// Stage 1: New user clean access
	$registration->step1();
}

?>
