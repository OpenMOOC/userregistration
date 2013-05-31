<?php

class sspmod_userregistration_ExtraData_GotoURL extends sspmod_userregistration_ExtraData_Base {

	protected $url;

	public function setURL($url)
	{
		$this->url = $url;
	}

	public function getURL()
	{
		return $this->url;
	}

	public function rebuild(Array $data = array())
	{
		if (count($data) == 0) {
			$this->data = array(
				'type' => 'goto',
				'url' => $this->url,
			);
		} else {
			parent::rebuild($data);
		}
	}
}
