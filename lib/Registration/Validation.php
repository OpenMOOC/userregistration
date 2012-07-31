<?php

class sspmod_userregistration_Registration_Validation {
	private $validators = NULL;

	public function __construct($fieldsDef, $usedFields) {
		foreach ($usedFields as $field) {
			$this->validators[$field] = $fieldsDef[$field]['validate'];
		}
	}


	public function validateInput(){

		$filtered = filter_input_array(INPUT_POST, $this->validators);
		// FIXME: Write failed validation values to log
		foreach($filtered as $field => $value){
			if(!$value){
				$rawValue = isset($_REQUEST[$field])?$_REQUEST[$field]:NULL;
				if(!$rawValue){
					throw new sspmod_userregistration_Error_UserException(
						'void_value',
						$field,
						'',
						'Validation of user input failed.'
						.' Field:'.$field
						.' is empty');
				}else{
					throw new sspmod_userregistration_Error_UserException(
						'illegale_value',
						$field,
						$rawValue,
						'Validation of user input failed.'
						.' Field:'.$field
						.' Value:'.$rawValue);
				}
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
					print_r(strlen($password));
					throw new sspmod_userregistration_Error_UserException('err_min_length_pw', $passwordPolicy['min.length']);
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
