<?php

class sspmod_userregistration_ExtraData_MailChangeToken extends sspmod_userregistration_ExtraData_Base {

	protected $token;

	public function __construct($token, $oldmail = '', $newmail = '', $lifetime = false)
	{
		$data = array(
			'type' => 'mail_change',
			'oldmail' => $oldmail,
			'newmail' => $newmail,
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
