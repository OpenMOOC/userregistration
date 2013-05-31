<?php

abstract class sspmod_userregistration_ExtraData_Base {
	protected $key;

	protected $data;

	protected $expire;

	public function __construct($key, Array $data = array(), $expire = false)
	{
		$this->key = (string)$key;
		$this->data = $data;
		$this->expire = $expire;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getExpire()
	{
		return $this->expire;
	}

	// Recreates this data using custom attributes
	public function rebuild(Array $data)
	{
		$this->data = $data;
	}
}
