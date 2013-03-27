<?php

class sspmod_userregistration_XHTML_Form {

	private $layout = array();
	private $values = array();
	private $toWrite = array();
	private $hidden = array();
	private $readonly = array();
	private $disabled = array();
	private $size = 30;
	private $actionEndpoint = '?';
	private $transAttr = NULL;
	private $transDesc = NULL;
	private $submitName = 'sender';
	private $submitValue = 'Submit';
	private $cancelButton = false;
	private $cancelURL = NULL;
	private $cancelText = NULL;
	private $tos = false;
	private $sendemail = false;


	public function __construct($fieldsDef = array(), $actionEndpoint = NULL){
		foreach ($fieldsDef as $name => $field) {
			$this->layout[$name] = $field['layout'];
		}
		if ($actionEndpoint) $this->actionEndpoint = $actionEndpoint;

		$config = SimpleSAML_Configuration::getInstance();
		$this->transAttr = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step1email.php', // Selected as a dummy
			'attributes');
		$this->transDesc = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step1email.php', // Selected as a dummy
			'userregistration:userregistration');

	}


	public function setValues($formValues){
		$this->values = array_merge($this->values, $formValues);
	}


	public function setSubmitter($value, $name = 'sender'){
		$this->submitName = $name;
		$this->submitValue = $value;
	}


	public function fieldsToShow($fields){
		$fields = SimpleSAML_Utilities::arrayize($fields);
		$this->toWrite = array_merge($this->toWrite, $fields);
	}


	public function addHiddenData($arrNameValue){
		$this->hidden = array_merge($this->hidden, $arrNameValue);
	}


	public function addTOS($tos){
		$this->tos = $tos;
	}

	public function addSendEmail($sendemail){
		$this->sendemail = $sendemail;
	}

	public function addCancelButton($text, $url){
		$this->cancelButton = true;
		$this->cancelText = $text;
		$this->cancelURL = $url;
	}

	/*
	 * String: field name
	 * or array of fieldnames
	 */
	public function setReadOnly($fields){
		$fields = SimpleSAML_Utilities::arrayize($fields);
		$this->readonly = array_merge($this->readonly, $fields);
	}

	/*
	 * String: field name
	 * or array of fieldnames
	 */
	public function setDisabled($fields){
		$fields = SimpleSAML_Utilities::arrayize($fields);
		$this->disabled = array_merge($this->disabled, $fields);
	}


	private function writeFormStart(){
		$format = '<form id="userregistration" action="%s" method="post"><table class="formTable">';
		$html = sprintf($format, $this->actionEndpoint);
		return $html;
	}


	private function writeFormEnd(){
		return '</table></form>';
	}

	private function writeFormElement($elementId){
		$html = '<tr class="element"><td class="labelcontainer">';
		$html .= $this->writeLabel($elementId);
		$html .= '</td><td>'.$this->writeInputControl($elementId);
		$html .= $this->writeControlDescription($elementId);
		$html .= '</td></tr>';

		return $html;
	}


	private function writeLabel($elementId){
		$format = '<label for="%s">%s:</label>';
		$trTag = strtolower('attribute_'.$elementId);
		$trLabel = htmlspecialchars($this->transDesc->t($trTag));
		// Got no translation, try again
		if( (bool)strstr($trLabel, 'not translated') ) {
			$trLabel = htmlspecialchars($this->transAttr->t($trTag));
		}
		$html = sprintf($format, $elementId, $trLabel);
		return $html;
	}


	private function writeInputControl($elementId){
		$value = isset($this->values[$elementId])?$this->values[$elementId]:'';
		$value = htmlspecialchars($value);
		if($this->actionEndpoint != 'delUser.php') {
			$type = $this->layout[$elementId]['control_type'];

			$attr = '';
			if(in_array($elementId, $this->readonly)){
				$attr .= 'readonly="readonly"';
			}
			if(in_array($elementId, $this->disabled)){
				$attr .= 'disabled="disabled"';
			}
			if(in_array($elementId, $this->disabled)){
				$attr .= 'disabled="disabled"';
			}

			if($type=='password') {
				$attr .= 'aria-controls="'.$elementId.'_simplePassMeter"';
			}
			if($type=='country') {
				return $this->writeCountrySelect($value, $attr);
			}

            $size = $this->size;
            if(isset($this->layout[$elementId]['size']) && is_numeric((int)$this->layout[$elementId]['size'])) {
                $size = $this->layout[$elementId]['size'];
            }

			$format = '<input class="'.($type=='password'? 'inputelement simplePassMeterInput':'inputelement').'" type="%s" id="%s" name="%s" value="%s" size="%s" %s '.(isset($this->layout[$elementId]['size'])? 'maxlength="'.$size.'"':''). ' />';

			$html = sprintf($format, $type, $elementId, $elementId, $value, $size, $attr);
		}
		else {
			$format = '<br>%s<input type="hidden" id="%s" name="%s" value="%s" >';
			$html = sprintf($format, $value, $elementId, $elementId, $value);
		}

		return $html;
	}


	private function writeControlDescription($elementId) {

		$format = '%s';
		$descId = $elementId.'_desc';
		$trDesc = htmlspecialchars($this->transDesc->t($descId) );
		if($this->actionEndpoint == 'delUser.php' || (bool)strstr($trDesc, 'not translated') ) {
			return '';
		}

		$html = '<p class="elementDescr">' . sprintf($format, $trDesc) . '</p>';
		return $html;
	}


	private function writeHidden(){
		$html = '';
		$format = '<input type="hidden" name="%s" value="%s" />';
		foreach($this->hidden as $name => $value){
			$html .= sprintf($format, $name, htmlspecialchars($value) );
		}

		return $html;
	}


	private function writeFormButtons(){
		$html = '';
		$format = '<tr><td></td><td>'
			.'<button type="submit" class="btn btn-primary" type="submit" name="%s">%s</button>';
		if ($this->cancelButton === true) {
			$format .= $this->writeCancel();
		}
		$format .= '</td></tr>';
		$trValue = htmlspecialchars($this->transDesc->t($this->submitValue));
		$html = sprintf($format, $this->submitName, $trValue);
		return $html;
	}

	private function writeTOS($tos){
		$template = new SimpleSAML_XHTML_Template(
		SimpleSAML_Configuration::getInstance(),
		'userregistration:step1_register.tpl.php',
		'userregistration:userregistration');

		$html = '<tr><td></td><td><input type="checkbox" name="tos" id="tos" value="tos"><label for="tos"> '.$template->t('tos').' (<a href="'.$tos.'" >'.$template->t('see_tos').'</a>)</label></td></tr>';
		return $html;
	}

	private function writeSendEmail()
	{
		$template = new SimpleSAML_XHTML_Template(
		SimpleSAML_Configuration::getInstance(),
		'userregistration:step1_register.tpl.php',
		'userregistration:userregistration');

		$html = '<tr><td></td><td><input type="checkbox" name="sendemail" id="sendemail" value="sendemail"><label for="sendemail"> '.$template->t('sendemail').'</label></td></tr>';
		return $html;
	}
	private function writeCancel(){
		$html = '<button class="btn" type="cancel" onclick="javascript:window.location=\'' 
			. $this->cancelURL.'\'; return false">'
			.$this->cancelText.'</button>';
		return $html;
	}


	private function writeCountrySelect($value, $attr){
		if(empty($value)) {
			$value = 'US';
		}

		$countries = array( 
			'AF'=>'AFGHANISTAN',
			'AL'=>'ALBANIA',
			'DZ'=>'ALGERIA',
			'AS'=>'AMERICAN SAMOA',
			'AD'=>'ANDORRA',
			'AO'=>'ANGOLA',
			'AI'=>'ANGUILLA',
			'AQ'=>'ANTARCTICA',
			'AG'=>'ANTIGUA AND BARBUDA',
			'AR'=>'ARGENTINA',
			'AM'=>'ARMENIA',
			'AW'=>'ARUBA',
			'AU'=>'AUSTRALIA',
			'AT'=>'AUSTRIA',
			'AZ'=>'AZERBAIJAN',
			'BS'=>'BAHAMAS',
			'BH'=>'BAHRAIN',
			'BD'=>'BANGLADESH',
			'BB'=>'BARBADOS',
			'BY'=>'BELARUS',
			'BE'=>'BELGIUM',
			'BZ'=>'BELIZE',
			'BJ'=>'BENIN',
			'BM'=>'BERMUDA',
			'BT'=>'BHUTAN',
			'BO'=>'BOLIVIA',
			'BA'=>'BOSNIA AND HERZEGOVINA',
			'BW'=>'BOTSWANA',
			'BV'=>'BOUVET ISLAND',
			'BR'=>'BRAZIL',
			'IO'=>'BRITISH INDIAN OCEAN TERRITORY',
			'BN'=>'BRUNEI DARUSSALAM',
			'BG'=>'BULGARIA',
			'BF'=>'BURKINA FASO',
			'BI'=>'BURUNDI',
			'KH'=>'CAMBODIA',
			'CM'=>'CAMEROON',
			'CA'=>'CANADA',
			'CV'=>'CAPE VERDE',
			'KY'=>'CAYMAN ISLANDS',
			'CF'=>'CENTRAL AFRICAN REPUBLIC',
			'TD'=>'CHAD',
			'CL'=>'CHILE',
			'CN'=>'CHINA',
			'CX'=>'CHRISTMAS ISLAND',
			'CC'=>'COCOS (KEELING) ISLANDS',
			'CO'=>'COLOMBIA',
			'KM'=>'COMOROS',
			'CG'=>'CONGO',
			'CD'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
			'CK'=>'COOK ISLANDS',
			'CR'=>'COSTA RICA',
			'CI'=>'COTE D IVOIRE',
			'HR'=>'CROATIA',
			'CU'=>'CUBA',
			'CY'=>'CYPRUS',
			'CZ'=>'CZECH REPUBLIC',
			'DK'=>'DENMARK',
			'DJ'=>'DJIBOUTI',
			'DM'=>'DOMINICA',
			'DO'=>'DOMINICAN REPUBLIC',
			'TP'=>'EAST TIMOR',
			'EC'=>'ECUADOR',
			'EG'=>'EGYPT',
			'SV'=>'EL SALVADOR',
			'GQ'=>'EQUATORIAL GUINEA',
			'ER'=>'ERITREA',
			'EE'=>'ESTONIA',
			'ET'=>'ETHIOPIA',
			'FK'=>'FALKLAND ISLANDS (MALVINAS)',
			'FO'=>'FAROE ISLANDS',
			'FJ'=>'FIJI',
			'FI'=>'FINLAND',
			'FR'=>'FRANCE',
			'GF'=>'FRENCH GUIANA',
			'PF'=>'FRENCH POLYNESIA',
			'TF'=>'FRENCH SOUTHERN TERRITORIES',
			'GA'=>'GABON',
			'GM'=>'GAMBIA',
			'GE'=>'GEORGIA',
			'DE'=>'GERMANY',
			'GH'=>'GHANA',
			'GI'=>'GIBRALTAR',
			'GR'=>'GREECE',
			'GL'=>'GREENLAND',
			'GD'=>'GRENADA',
			'GP'=>'GUADELOUPE',
			'GU'=>'GUAM',
			'GT'=>'GUATEMALA',
			'GN'=>'GUINEA',
			'GW'=>'GUINEA-BISSAU',
			'GY'=>'GUYANA',
			'HT'=>'HAITI',
			'HM'=>'HEARD ISLAND AND MCDONALD ISLANDS',
			'VA'=>'HOLY SEE (VATICAN CITY STATE)',
			'HN'=>'HONDURAS',
			'HK'=>'HONG KONG',
			'HU'=>'HUNGARY',
			'IS'=>'ICELAND',
			'IN'=>'INDIA',
			'ID'=>'INDONESIA',
			'IR'=>'IRAN, ISLAMIC REPUBLIC OF',
			'IQ'=>'IRAQ',
			'IE'=>'IRELAND',
			'IL'=>'ISRAEL',
			'IT'=>'ITALY',
			'JM'=>'JAMAICA',
			'JP'=>'JAPAN',
			'JO'=>'JORDAN',
			'KZ'=>'KAZAKSTAN',
			'KE'=>'KENYA',
			'KI'=>'KIRIBATI',
			'KP'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF',
			'KR'=>'KOREA REPUBLIC OF',
			'KW'=>'KUWAIT',
			'KG'=>'KYRGYZSTAN',
			'LA'=>'LAO PEOPLES DEMOCRATIC REPUBLIC',
			'LV'=>'LATVIA',
			'LB'=>'LEBANON',
			'LS'=>'LESOTHO',
			'LR'=>'LIBERIA',
			'LY'=>'LIBYAN ARAB JAMAHIRIYA',
			'LI'=>'LIECHTENSTEIN',
			'LT'=>'LITHUANIA',
			'LU'=>'LUXEMBOURG',
			'MO'=>'MACAU',
			'MK'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
			'MG'=>'MADAGASCAR',
			'MW'=>'MALAWI',
			'MY'=>'MALAYSIA',
			'MV'=>'MALDIVES',
			'ML'=>'MALI',
			'MT'=>'MALTA',
			'MH'=>'MARSHALL ISLANDS',
			'MQ'=>'MARTINIQUE',
			'MR'=>'MAURITANIA',
			'MU'=>'MAURITIUS',
			'YT'=>'MAYOTTE',
			'MX'=>'MEXICO',
			'FM'=>'MICRONESIA, FEDERATED STATES OF',
			'MD'=>'MOLDOVA, REPUBLIC OF',
			'MC'=>'MONACO',
			'MN'=>'MONGOLIA',
			'MS'=>'MONTSERRAT',
			'MA'=>'MOROCCO',
			'MZ'=>'MOZAMBIQUE',
			'MM'=>'MYANMAR',
			'NA'=>'NAMIBIA',
			'NR'=>'NAURU',
			'NP'=>'NEPAL',
			'NL'=>'NETHERLANDS',
			'AN'=>'NETHERLANDS ANTILLES',
			'NC'=>'NEW CALEDONIA',
			'NZ'=>'NEW ZEALAND',
			'NI'=>'NICARAGUA',
			'NE'=>'NIGER',
			'NG'=>'NIGERIA',
			'NU'=>'NIUE',
			'NF'=>'NORFOLK ISLAND',
			'MP'=>'NORTHERN MARIANA ISLANDS',
			'NO'=>'NORWAY',
			'OM'=>'OMAN',
			'PK'=>'PAKISTAN',
			'PW'=>'PALAU',
			'PS'=>'PALESTINIAN TERRITORY, OCCUPIED',
			'PA'=>'PANAMA',
			'PG'=>'PAPUA NEW GUINEA',
			'PY'=>'PARAGUAY',
			'PE'=>'PERU',
			'PH'=>'PHILIPPINES',
			'PN'=>'PITCAIRN',
			'PL'=>'POLAND',
			'PT'=>'PORTUGAL',
			'PR'=>'PUERTO RICO',
			'QA'=>'QATAR',
			'RE'=>'REUNION',
			'RO'=>'ROMANIA',
			'RU'=>'RUSSIAN FEDERATION',
			'RW'=>'RWANDA',
			'SH'=>'SAINT HELENA',
			'KN'=>'SAINT KITTS AND NEVIS',
			'LC'=>'SAINT LUCIA',
			'PM'=>'SAINT PIERRE AND MIQUELON',
			'VC'=>'SAINT VINCENT AND THE GRENADINES',
			'WS'=>'SAMOA',
			'SM'=>'SAN MARINO',
			'ST'=>'SAO TOME AND PRINCIPE',
			'SA'=>'SAUDI ARABIA',
			'SN'=>'SENEGAL',
			'SC'=>'SEYCHELLES',
			'SL'=>'SIERRA LEONE',
			'SG'=>'SINGAPORE',
			'SK'=>'SLOVAKIA',
			'SI'=>'SLOVENIA',
			'SB'=>'SOLOMON ISLANDS',
			'SO'=>'SOMALIA',
			'ZA'=>'SOUTH AFRICA',
			'GS'=>'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
			'ES'=>'SPAIN',
			'LK'=>'SRI LANKA',
			'SD'=>'SUDAN',
			'SR'=>'SURINAME',
			'SJ'=>'SVALBARD AND JAN MAYEN',
			'SZ'=>'SWAZILAND',
			'SE'=>'SWEDEN',
			'CH'=>'SWITZERLAND',
			'SY'=>'SYRIAN ARAB REPUBLIC',
			'TW'=>'TAIWAN, PROVINCE OF CHINA',
			'TJ'=>'TAJIKISTAN',
			'TZ'=>'TANZANIA, UNITED REPUBLIC OF',
			'TH'=>'THAILAND',
			'TG'=>'TOGO',
			'TK'=>'TOKELAU',
			'TO'=>'TONGA',
			'TT'=>'TRINIDAD AND TOBAGO',
			'TN'=>'TUNISIA',
			'TR'=>'TURKEY',
			'TM'=>'TURKMENISTAN',
			'TC'=>'TURKS AND CAICOS ISLANDS',
			'TV'=>'TUVALU',
			'UG'=>'UGANDA',
			'UA'=>'UKRAINE',
			'AE'=>'UNITED ARAB EMIRATES',
			'GB'=>'UNITED KINGDOM',
			'US'=>'UNITED STATES',
			'UM'=>'UNITED STATES MINOR OUTLYING ISLANDS',
			'UY'=>'URUGUAY',
			'UZ'=>'UZBEKISTAN',
			'VU'=>'VANUATU',
			'VE'=>'VENEZUELA',
			'VN'=>'VIET NAM',
			'VG'=>'VIRGIN ISLANDS, BRITISH',
			'VI'=>'VIRGIN ISLANDS, U.S.',
			'WF'=>'WALLIS AND FUTUNA',
			'EH'=>'WESTERN SAHARA',
			'YE'=>'YEMEN',
			'YU'=>'YUGOSLAVIA',
			'ZM'=>'ZAMBIA',
			'ZW'=>'ZIMBABWE'			
		);		
		$html = '<select name="country" id="country" '.$attr.' >';
		foreach ($countries as $code => $name) {
			$html .= '<option value="' . $code . '" ' . ($value == $code ? 'selected="selected"' : '') . '>' . $name . '</option>';
		}
	
		$html .= '</select>';
		return $html;
	}

	public function genFormHtml(){
		$html = '';

		$html .= $this->writeFormStart();
		if(count($this->hidden) > 0){
			$html .= $this->writeHidden();
		}
		foreach($this->toWrite as $fId){
			switch ($fId){
			case NULL:
				break;
			default:
				$html .= $this->writeFormElement($fId);
			}
		}
		if ($this->tos) {
			$html .= $this->writeTOS($this->tos);
		}
		if ($this->sendemail) {
			$html .= $this->writeSendEmail();
		}
		$html .= $this->writeFormButtons();

		$html .= $this->writeFormEnd();

		return $html;
	}

  } //end class


?>
