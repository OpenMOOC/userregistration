<?php

	$passwordPolicy = $this->data['passwordPolicy'];

echo '
<script>
$(document).ready(function() {

  $(\'#'.$this->data['passwordField'].'\').simplePassMeter({
	"offset": 5,
	"requirements": {'."\n";
		if(array_key_exists('min.length', $passwordPolicy)) {
			echo '      "minLength": {"value": '.$passwordPolicy['min.length'].', "message": "'.$this->t('err_min_length_pw', array('%VAR1%' => $passwordPolicy['min.length'])).'"},'."\n";
		}
	if(array_key_exists('require.lowercaseUppercase', $passwordPolicy) && $passwordPolicy['require.lowercaseUppercase']) {
		echo '      "lower": {"value": true, "message": "'.$this->t('err_lowercaseUppercase_pw').'"}, '."\n";
		echo '      "upper": {"value": true, "message": "'.$this->t('err_lowercaseUppercase_pw').'"}, '."\n";
	}
	if(array_key_exists('require.digits', $passwordPolicy) && $passwordPolicy['require.digits']) {
		echo '      "numbers": { "value": true, "message": "'.$this->t('err_digits_pw').'"},'."\n";
	}
	if(array_key_exists('require.any.non.alphanumerics', $passwordPolicy) && $passwordPolicy['require.any.non.alphanumerics']) {
		echo '      "special": {"value": true, "message": "'.$this->t('err_non_alphanumerics_pw').'"},'."\n";
	}
	if(isset($this->data['forbiddenValues']) && !empty($this->data['forbiddenValues'])) {
		echo '      "noMatchEmailField": { "value":true, 
											"message": "'.$this->t('err_contains_param_pw', array('%VAR1%' => $this->data['forbiddenValuesFieldnames'])).'",
											"callback": function(password, value) {
												var str = $(\'#'.$this->data['passwordField'].'\')[0].value;'."\n";
												foreach($this->data['forbiddenValues'] as $forbiddenValue) {
													echo 'if(str.match("'.$forbiddenValue.'")) {
															return false;
														  }';
												}
												echo '	return true;
											},
										}'."\n";
	}

echo '
	},
	"ratings": [
		{
			"minScore": 0,
			"className": "meterFail",
			"text": "'.$this->t('meter_need_stronger').'"
		},
		{
			"minScore": 25,
			"className": "meterWarn",
			"text": "'.$this->t('meter_weak').'"
		},
		{
			"minScore": 40,
			"className": "meterGood",
			"text": "'.$this->t('meter_good').'"
		},
		{
			"minScore": 50,
			"className": "meterGood",
			"text": "'.$this->t('meter_very_good').'"
		},
		{
			"minScore": 75,
			"className": "meterExcel",
			"text": "'.$this->t('meter_excellent').'"
		}
	]
  })
});'."\n";

echo '</script>
';

?>
