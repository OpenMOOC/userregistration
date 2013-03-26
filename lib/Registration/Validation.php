<?php

class sspmod_userregistration_Registration_Validation {
	private $validators = array();
	private $optionals = array();
	private $size = array();

	public function __construct($fieldsDef, $usedFields, $form_name = '') {
		foreach ($usedFields as $field) {
			$this->validators[$field] = array();
			$this->validators[$field] = $fieldsDef[$field]['validate'];
			if (isset($fieldsDef[$field]['layout']) && isset($fieldsDef[$field]['layout']['optional'])) {
				$opt = $fieldsDef[$field]['layout']['optional'];
				$is_optional = false;
				if (is_array($opt)) {
					$is_optional = in_array($form_name, $opt);
				} else {
					$is_optional = $opt;
				}
				if ($is_optional === true) {
					$this->optionals[] = $field;
				}
			}
			if (isset($fieldsDef[$field]['layout']) 
				&& isset($fieldsDef[$field]['layout']['size'])
				&& is_numeric((int)$fieldsDef[$field]['layout']['size'])) {
					$this->size[$field] = (int)$fieldsDef[$field]['layout']['size'];
			}
		}
	}


	public function validateInput(){
		$config = SimpleSAML_Configuration::getInstance();
		$transAttr = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step1email.php', // Selected as a dummy
			'attributes');
		$transDesc = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step1email.php', // Selected as a dummy
			'userregistration:userregistration');


		$filtered = filter_input_array(INPUT_POST, $this->validators);
		// FIXME: Write failed validation values to log
		foreach($filtered as $field => $value){
			if(empty($value)) {
				$tag = strtolower('attribute_'.$field);
				$fieldTranslated = htmlspecialchars($transDesc->t($tag));
				// Got no translation, try again
				if ((bool)strstr($fieldTranslated, 'not translated') ) {
					$fieldTranslated = htmlspecialchars($transAttr->t($tag));
				}

				$rawValue = isset($_REQUEST[$field])?$_REQUEST[$field]:NULL;

				if (empty($rawValue)) {
					if(!in_array($field, $this->optionals)) {
						throw new sspmod_userregistration_Error_UserException(
							'void_value',
							"'$fieldTranslated'",
							'',
							'Validation of user input failed.'
							.' Field:'.$field
							.' is empty');
					}
				} else {
					throw new sspmod_userregistration_Error_UserException(
						'illegale_value',
						"'$fieldTranslated'",
						$rawValue,
						'Validation of user input failed.'
						.' Field:'.$field
						.' Value:'.$rawValue);
				}
			}
			else {
				# sanitize data
				$filtered[$field] = strip_tags($value);
			}
		}
		foreach($this->size as $field => $size){
			if(isset($_REQUEST[$field]) && strlen($_REQUEST[$field]) > (int)$size) {
				$tag = strtolower('attribute_'.$field);
				$fieldTranslated = htmlspecialchars($transDesc->t($tag));
				throw new sspmod_userregistration_Error_UserException(
					'illegale_length_value',
					"'$fieldTranslated'",
					$size,
					'Validation of user lenght input failed.'
					.' Field:'.$field
					.' Max size:'.$size);
			}
		}

		return $filtered;
	}

	// When creating new user, $attributes contains values from form, when changing
	// password $attributes contains values from session (each param is an array)
	public function validatePolicyPassword($passwordPolicy, $attributes, $password){
		if(is_array($passwordPolicy)) {
			if(array_key_exists('min.length', $passwordPolicy)) {
				if(strlen($password) < (int)$passwordPolicy['min.length']) {
					throw new sspmod_userregistration_Error_UserException('err_min_length_pw', $passwordPolicy['min.length']);
				}
			}
			if(array_key_exists('max.length', $passwordPolicy)) {
				if(strlen($password) > (int)$passwordPolicy['max.length']) {
					throw new sspmod_userregistration_Error_UserException('err_max_length_pw', $passwordPolicy['max.length']);
				}
			}
			if(array_key_exists('require.lowercaseUppercase', $passwordPolicy) && $passwordPolicy['require.lowercaseUppercase']) {
				if((strcmp($password, strtolower($password)) == 0) && (strcmp($password, strtoupper($password)) == 0)) {
					throw new sspmod_userregistration_Error_UserException('err_lowercaseUppercase_pw');
				}
			}
			if(array_key_exists('require.digits', $passwordPolicy) && $passwordPolicy['require.digits']) {
				if(preg_match("/\d/", $password) < 0) {
					throw new sspmod_userregistration_Error_UserException('err_digits_pw');
				}
			}
			if(array_key_exists('require.any.non.alphanumerics', $passwordPolicy) && $passwordPolicy['require.any.non.alphanumerics']) {
				if(ctype_alnum($password)) {
					throw new sspmod_userregistration_Error_UserException('err_non_alphanumerics_pw');
				}
			}
			if(array_key_exists('no.contains', $passwordPolicy)) {
				$no_contains = $passwordPolicy['no.contains'];
				if(is_array($no_contains) && !empty($no_contains)) {
					foreach($no_contains as $key) {
						if(array_key_exists($key, $attributes) && !empty($attributes[$key])) {
							$value = $attributes[$key];
							if(is_array($value)) {
								$value = $value[0];
							}
							if(stripos($password, $value) !== false) {
								throw new sspmod_userregistration_Error_UserException('err_contains_param_pw', $key, '', "The password can't be like the ".$key);
							}
						}
					}
				}
			}
			if(array_key_exists('check.dicctionaries', $passwordPolicy)) {
				$dicts = $passwordPolicy['check.dicctionaries'];
				if(is_array($dicts) && !empty($dicts)) {
					$hookfile = SimpleSAML_Module::getModuleDir('userregistration') . '/hooks/';
					foreach($dicts as $dict) {
						/* TODO CrackLib support http://www.php.net/manual/en/ref.crack.php
						if (!function_exists('crack_opendict')) {
							throw new sspmod_userregistration_Error_UserException('err_no_cracklib_pw');
						}
						try {
							$dictionary = crack_opendict($hookfile.$dict);
							$check = crack_check($dictionary, $password);
							crack_closedict($dictionary);
						}
						catch(Exception $e) {
							throw new sspmod_userregistration_Error_UserException($e->getMessage());
						}
						if(!$check) {
							throw new sspmod_userregistration_Error_UserException('err_common_pw', $dict);
						}
						*/
						$file = @fopen($hookfile.$dict, "r");
						if($file) {
							while (!feof($file)) {
								$pw = strtolower(trim(fgets($file,4096)));
								if(strcmp($password,$pw) == 0) { 
									throw new sspmod_userregistration_Error_UserException('err_common_pw', $dict);
								}
							}
						}
					}
				}
			}
		}
	}

	public function getRawInput(){
		$input = array();
		foreach($this->validators as $fn => $fv){
			if(isset($_REQUEST[$fn])){
				$input[$fn] = $_REQUEST[$fn];
			}
		}
		return $input;
	}

} // end Validation


?>
