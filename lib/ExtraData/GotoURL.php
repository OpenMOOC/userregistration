<?php

class sspmod_userregistration_ExtraData_GotoURL extends sspmod_userregistration_ExtraData_Base {

	public function __construct($email, $url = '')
	{
		$data = array(
			'type' => 'goto',
			'url' => $url,
		);
		parent::__construct(
			'goto:' . $email,
			$data
		);
	}
}
