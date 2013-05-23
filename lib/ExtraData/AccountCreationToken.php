<?php

class sspmod_userregistration_ExtraData_AccountCreationToken extends sspmod_userregistration_ExtraData_Base {

	protected $token;

	public function __construct($token, $email = '', $lifetime = false)
	{
		$data = array(
			'type' => 'account_creation',
			'email' => $email,
		);
		parent::__construct(
			'token:' . $token,
			$data,
			$lifetime
		);

		$this->token = $token;
	}

	public function getToken()
	{
		return $this->token;
	}
}
