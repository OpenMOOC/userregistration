<?php

class sspmod_userregistration_ExtraData_MailChangeToken extends sspmod_userregistration_ExtraData_Base {

	protected $oldmail;

	protected $newmail;

	public function setOldEmail($oldmail)
	{
		$this->oldmail = $oldmail;
	}

	public function getOldEmail()
	{
		return $this->oldmail;
	}

	public function setNewEmail($newmail)
	{
		$this->newmail = $newmail;
	}

	public function getNewEmail()
	{
		return $this->newmail;
	}

	public function rebuild(Array $data = array())
	{
		if (count($data) == 0) {
			$this->data = array(
				'type' => 'mail_change',
				'oldmail' => $this->oldmail,
				'newmail' => $this->newmail,
			);
		} else {
			parent::rebuild($data);
		}
	}
}
