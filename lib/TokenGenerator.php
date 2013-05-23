<?php

class sspmod_userregistration_TokenGenerator {

	// 28 bytes long
	protected static $tokenLength = 28;

	private $lifetime;

	public function __construct($lifetime)
	{
		$this->lifetime = $lifetime;
	}

	// Generates a new token
	public function generate()
	{
		return bin2hex(openssl_random_pseudo_bytes(self::$tokenLength));
	}

	public function newAccountCreationToken($email)
	{
		return new sspmod_userregistration_ExtraData_AccountCreationToken(
			$this->generate(),
			$email,
			$this->lifetime
		);
	}
}
