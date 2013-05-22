<?php

class sspmod_userregistration_Registration {
	private $steps;

	private $config;

	private $formFields;

	private $tos;

	private $systemName;

	private $attributes;

	private $customNavigation;

	private $mailoptions;

	private $knownEmailProviders;

	private $userInfo;

	private $store;

	private $tokenManager;

	private $as;


	public function __construct($config)
	{
		$this->config = $config;
		$this->customNavigation = TRUE;
		$this->steps = new sspmod_userregistration_XHTML_Steps();
		$this->store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();
	}

	public function setFormFields($formFields)
	{
		$this->formFields = $formFields;
	}

	public function setTOS($tos)
	{
		$this->tos = $tos;
	}

	public function setSystemName($systemName)
	{
		$this->systemName = $systemName;
	}

	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;
	}

	public function setCustomNavigation($customNavigation)
	{
		$this->customNavigation = $customNavigation;
	}

	public function setMailOptions($mailoptions)
	{
		$this->mailoptions = $mailoptions;

		// Initialize token manager
		$this->tokenManager = new sspmod_userregistration_TokenManagement($this->mailoptions['token.lifetime']);
	}

	public function setKnownEmailProviders($known_email_providers)
	{
		$this->knownEmailProviders = $known_email_providers;
	}

	public function setAs($as)
	{
		$this->as = $as;
	}

	public function step1($error = null)
	{
		$this->steps->setCurrent(1);

		// URL where the user wants to be redirected after registering
		$gotoURL = isset($_REQUEST['goto']) ? $_REQUEST['goto'] : null;

		$formGen = new sspmod_userregistration_XHTML_Form($this->formFields, 'newUser.php');

		$showFields = sspmod_userregistration_Util::getFieldsFor('new_user');

		$formGen->fieldsToShow($showFields);

		if (!empty($this->tos)) {
			$formGen->addTOS($this->tos);
		}

		if ($gotoURL !== null) {
			$formGen->addHiddenData(array('goto' => $gotoURL));
		}

		$html = new SimpleSAML_XHTML_Template(
			$this->config,
			'userregistration:step1_register.tpl.php',
			'userregistration:userregistration');

		// Are we coming from an error?
		if ($error !== null) {
			$values = $this->validator->getRawInput();
			$formGen->setValues($values);
			if ($error->getMesgId() == 'uid_taken_but_not_verified') {
				$email = $this->userInfo[$this->store->userRegisterEmailAttr];
				$html->data['refreshtoken'] = true;
				$html->data['email'] = $email;
			} elseif ($error->getMesgId() == 'uid_taken') {
				$html->data['url_lostpassword'] = SimpleSAML_Module::getModuleURL('userregistration/lostPassword.php');
			}

			$error_msg = $html->t(
				$error->getMesgId(),
				$error->getTrVars()
			);

			$html->data['error'] = htmlspecialchars($error_msg);
		}

		$formGen->setSubmitter('register');
		$formHtml = $formGen->genFormHtml();

		$html->data['stepsHtml'] = $this->steps->generate();
		$html->data['formHtml'] = $formHtml;

		$html->data['systemName'] = $this->systemName;
		$html->data['customNavigation'] = $this->customNavigation;
		$html->show();
	}

	// Stage 2: send email token
	public function step2($refresh_token = FALSE)
	{
		try {
			$this->steps->setCurrent(2);

			if ($refresh_token === FALSE) {
				// Add user object
				$listValidate = sspmod_userregistration_Util::getFieldsFor('new_user');

				$this->validator = new sspmod_userregistration_Registration_Validation(
					$this->formFields,
					$listValidate);
				$validValues = $this->validator->validateInput();

				$this->userInfo = sspmod_userregistration_Util::processInput(
					$validValues,
					$listValidate,
					$this->attributes
				);

				if(!empty($this->tos) && !array_key_exists('tos', $_POST)) {
					throw new sspmod_userregistration_Error_UserException('tos_failed');
				}

				$this->store->addUser($this->userInfo);

				$email = $this->userInfo[$this->store->userRegisterEmailAttr];
			} else {
				$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
				if ($email === null || $email === false) {
					// Dirty hack
					$url = SimpleSAML_Utilities::selfURL();
					header("Location: " . $url);
					die();
				}
			}

			// URL where the user wants to be redirected after registering
			$gotoURL = isset($_REQUEST['goto']) ? $_REQUEST['goto'] : null;

			$token = $this->tokenManager->generate($email);

			if ($gotoURL !== null && $gotoURL != '') {
				$this->tokenManager->addGotoURL($email, $gotoURL);
			}

			$url = SimpleSAML_Utilities::selfURL();

			$registerurl = SimpleSAML_Utilities::addURLparameter(
				$url,
				array(
					'token' => $token
				)
			);

			$tokenExpiration = $this->mailoptions['token.lifetime'];
			$mail_data = array(
				'email' => $email,
				'tokenLifetime' => $tokenExpiration,
				'registerurl' => $registerurl,
				'systemName' => $this->systemName,
			);

			sspmod_userregistration_Util::sendEmail(
				$email,
				$this->mailoptions['subject'],
				'userregistration:mail1_token.tpl.php',
				$mail_data
			);

			$html = new SimpleSAML_XHTML_Template(
				$this->config,
				'userregistration:step2_sent.tpl.php',
				'userregistration:userregistration');
			$html->data['stepsHtml'] = $this->steps->generate();
			$html->data['email'] = $email;
			$html->data['systemName'] = $this->systemName;
			$html->data['customNavigation'] = $this->customNavigation;

			// Email service provider helper
			$provider = new sspmod_userregistration_EmailProviderGuess(
				$email,
				$this->knownEmailProviders
			);
			if ($provider->isAKnownEmailProvider()) {
				$html->data['emailProvider'] = $provider->getProvider();
			}
			$html->show();
		} catch (Exception $e) {
			return $e;
		}
	}


	// Stage 3: User clicked on verification URL in email
	public function step3($error = null)
	{
		$this->steps->setCurrent(3);
		try {
			$token = $_REQUEST['token'];

			$html = new SimpleSAML_XHTML_Template(
				$this->config,
				'userregistration:step3_password.tpl.php',
				'userregistration:userregistration');
			$html->data['stepsHtml'] = $this->steps->generate();

			if ($error === null || $error->getMesgId() != 'invalid_token') {
				// Get token
				$token_data = $this->tokenManager->getDetails($token);

				if ($token_data === false) {
					throw new sspmod_userregistration_Error_UserException('invalid_token');
				}

				$email = $token_data['email'];

				$formGen = new sspmod_userregistration_XHTML_Form($this->formFields, 'newUser.php');

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
						$this->store->userRegisterEmailAttr => $email
					)
				);

				$formGen->setSubmitter('register');
				$formHtml = $formGen->genFormHtml();
				$html->data['formHtml'] = $formHtml;
			}

			// Error message
			if ($error !== null) {
				$error_msg = $html->t(
					$error->getMesgId(),
					$error->getTrVars()
				);

				$html->data['error'] = htmlspecialchars($error_msg);

				if ($error->getMesgId() == 'invalid_token') {
					$html->data['refreshtoken'] = true;
				}
			}

			if(!empty($this->store->passwordPolicy)) {
				$html->data['passwordPolicy'] = $this->store->passwordPolicy;
				$html->data['passwordPolicytpl'] = SimpleSAML_Module::getModuleDir('userregistration').'/templates/password_policy_tpl.php';
				$html->data['passwordField'] = 'pw1';
			}

			$html->data['customNavigation'] = $this->customNavigation;
			$html->show();
		} catch (sspmod_userregistration_Error_UserException $e){
			return $e;
		}
	}


	public function step4()
	{
		try {
			
			$this->steps->setCurrent(4);
			
			//  Validate token
			$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;
			$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;

			if ($token === null || $email === null) {
				throw new sspmod_userregistration_Error_UserException('invalid_token');
			}

			$token_data = $this->tokenManager->getDetails($token);
			if ($token_data === false || $token_data['email'] != $email) {
				throw new sspmod_userregistration_Error_UserException('invalid_token');
			}

			$listValidate = sspmod_userregistration_Util::getFieldsFor('first_password');
			$this->validator = new sspmod_userregistration_Registration_Validation(
				$this->formFields,
				$listValidate);
			$validValues = $this->validator->validateInput();

			$this->userInfo = sspmod_userregistration_Util::processInput(
				$validValues,
				$listValidate,
				$this->attributes
			);

			// Adding affiliation (student) when a user is registered
			$this->userInfo['eduPersonAffiliation'] = 'student';

			$newPw = sspmod_userregistration_Util::validatePassword($validValues);
			$this->validator->validatePolicyPassword(
				$this->store->passwordPolicy,
				$this->userInfo,
				$newPw
			);

			if (isset($this->userInfo['userPassword'])) {
				$this->userInfo['userPassword'] = $this->store->encrypt_pass(
					$this->userInfo['userPassword']
				);
			}

			$this->store->updateUser($_POST['email'], $this->userInfo);

			$html = new SimpleSAML_XHTML_Template(
				$this->config,
				'userregistration:step4_complete.tpl.php',
				'userregistration:userregistration');

			$html->data['systemName'] = $this->systemName;
			$html->data['customNavigation'] = $this->customNavigation;
			$html->data['stepsHtml'] = $this->steps->generate();

			// Retrieve goto URL
			$gotoURL = $this->tokenManager->getGotoURL($email);
			if ($gotoURL !== false) {
				$this->tokenManager->delete($email . ':goto');
				$html->data['goto'] = $this->as->getLoginURL($gotoURL);
			}

			$html->show();

			$this->tokenManager->delete($token);
		} catch (sspmod_userregistration_Error_UserException $e) {
			return $e;
		}
	}
}
