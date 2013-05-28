<?php

class sspmod_userregistration_ExtraData_MailChangeToken extends sspmod_userregistration_ExtraData_Base {

	protected $oldmail;

	protected $newmail;

	public function setOldMail($oldmail)
	{
		$this->oldmail = $oldmail;
	}

	public function getOldMail()
	{
		return $this->oldmail;
	}

	public function setNewMail($newmail)
	{
		$this->newmail = $newmail;
	}

	public function getNewMail()
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
