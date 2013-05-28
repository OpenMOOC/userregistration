<?php

class sspmod_userregistration_Util {

	public static function getFieldsFor($form_name)
	{
		$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		$defined_fields = $uregconf->getArray('formFields');
		$fields = array();
		foreach ($defined_fields as $fieldName => $options){
			if (isset($options['layout']['show']) 
				&& is_array($options['layout']['show'])
				&& in_array($form_name, $options['layout']['show'])) {
					switch($fieldName){
					case 'userPassword':
						$fields[] = 'pw1';
						$fields[] = 'pw2';
						break;
					case 'pw1':
					case 'pw2':
						// Ignore these two
						break;
					default:
						$fields[] = $fieldName;
					}
				}
		}
		return $fields;
	}

	public static function getReadOnlyFieldsFor($form_name)
	{
		$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		$defined_fields = $uregconf->getArray('formFields');
		$read_only_fields = array();
		foreach ($defined_fields as $fieldName => $options){
			if (isset($options['layout']['read_only'])
				&& is_array($options['layout']['read_only'])
				&& in_array($form_name, $options['layout']['read_only'])) {
					$read_only_fields[] = $fieldName;
				}
		}
		return $read_only_fields;
	}


	public static function checkLoggedAndSameAuth() {
		$session = SimpleSAML_Session::getInstance();
		if($session->isAuthenticated()) {
			$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
			/* Get a reference to our authentication source. */
			$asId = $uregconf->getString('auth');
			if($session->getAuthority() == $asId) {
				return new SimpleSAML_Auth_Simple($asId);
			}
		}
		return false;
	}


	public static function processInput($fieldValues, $wanted, $attributeDefinitions)
	{
		$hookfile = SimpleSAML_Module::getModuleDir('userregistration') . '/hooks/hook_attributes.php';
		include_once($hookfile);
		return processInput($fieldValues, $wanted, $attributeDefinitions);
	}


	public static function filterAsAttributes($asAttributes, $reviewAttr, $attributeDefinitions){
		$hookfile = SimpleSAML_Module::getModuleDir('userregistration') . '/hooks/hook_attributes.php';
		include_once($hookfile);
		return filterAsAttributes($asAttributes, $reviewAttr, $attributeDefinitions);
	}

	public static function validatePassword($fieldValues){
		if($fieldValues['pw1'] == $fieldValues['pw2']){
			return $fieldValues['pw1'];
		}else{
			throw new sspmod_userregistration_Error_UserException('err_retype_pw');
		}
	}

	public static function sendEmail($to, $subject, $template, $data)
	{
		$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		$config = SimpleSAML_Configuration::getInstance();
		$mailoptions = $uregconf->getArray('mail');

		$mailt = new SimpleSAML_XHTML_Template(
			$config,
			$template,
			'userregistration:userregistration');

		// Additional translations. Use dummy template
		$trans = new SimpleSAML_XHTML_Template(
			$config,
			$template,
			'login'
		);

		$mailt->data = $data;

		$mailer = new sspmod_userregistration_XHTML_Mailer(
			$to,
			$subject,
			$mailoptions['from'],
			NULL,
			$mailoptions['replyto']);
		$mailer->setTemplate($mailt);
		$mailer->send();
	}

	public static function checkIfAvailableMail($newmail, $store, $attributes, $mail_param, $uid_param) {
		foreach (array('irisMailAlternateAddress', $mail_param) as $check) {
			if ($store->isRegistered($check, $newmail)) {
				$user_with_mail = $store->findAndGetUser($check, $newmail, true);

				if (!empty($user_with_mail)) {
					if ($user_with_mail[$uid_param][0] != $attributes[$uid_param][0]) {
						throw new sspmod_userregistration_Error_UserException('mail_already_registered');
					}
				}
			}
		}
	}

}

?>
