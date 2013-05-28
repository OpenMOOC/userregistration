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
		$result = new sspmod_userregistration_ExtraData_AccountCreationToken(
			$this->generate(),
			array(),
			$this->lifetime
		);

		$result->setEmail($email);
		$result->rebuild();

		return $result;
	}

	public function newMailChangeToken($oldmail, $newmail)
	{
		$result = new sspmod_userregistration_ExtraData_MailChangeToken(
			$this->generate(),
			array(),
			$this->lifetime
		);

		$result->setOldMail($oldmail);
		$result->setNewMail($newmail);
		$result->rebuild();

		return $result;
	}
}
