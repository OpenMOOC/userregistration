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
	private $tos = false;


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


	private function writeFormSubmit(){
		$html = '';
		$format = '<tr><td></td><td><input class="btn" type="submit" name="%s" value="%s" /></td></tr>';
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
		$html .= $this->writeFormSubmit();

		$html .= $this->writeFormEnd();

		return $html;
	}

  } //end class


?>
