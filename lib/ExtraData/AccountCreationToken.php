<?php

class sspmod_userregistration_ExtraData_AccountCreationToken extends sspmod_userregistration_ExtraData_Base {

	protected $email;

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function rebuild(Array $data = array())
	{
		if (count($data) == 0) {
			$this->data = array(
				'type' => 'account_creation',
				'email' => $this->email,
			);
		} else {
			parent::rebuild($data);
		}
	}
}
