<?php

class sspmod_userregistration_XHTML_Steps {

	private $current;

	private $trans;

	private $max_steps;


	public function __construct($max_steps = 4, $current = 1)
	{
		$this->current = $current;
		$this->max_steps = $max_steps;
		$config = SimpleSAML_Configuration::getInstance();
		$this->trans = new SimpleSAML_XHTML_Template(
			$config,
			'userregistration:step1email.php', // Selected as a dummy
			'userregistration:userregistration');
	}

	public function setCurrent($current) {
		$this->current = $current;
	}

	public function generate()
	{
		$html = '<div class="steps">';
		for ($i=1;$i<=$this->max_steps;$i++) {
			$html .= '<span';
			if ($i < $this->current) {
				$html .= ' class="passed">';
			} elseif ($i == $this->current) {
				$html .= ' class="current">';
			} else {
				$html .= '>';
			}
			$html .= $i . '. ' . $this->trans->t('step_' . $i);
			$html .= '</span>';
		}

		$html .= '</div>';

		return $html;
	}

}
