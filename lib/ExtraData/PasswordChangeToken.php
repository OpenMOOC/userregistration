<?php

class sspmod_userregistration_ExtraData_PasswordChangeToken extends sspmod_userregistration_ExtraData_AccountCreationToken {
	public function rebuild(Array $data = array())
	{
		if (count($data) == 0) {
			$this->data = array(
				'type' => 'password_change',
				'email' => $this->email,
			);
		} else {
			parent::rebuild($data);
		}
	}
}
