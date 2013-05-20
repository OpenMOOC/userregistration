<?php

class sspmod_userregistration_EmailProviderGuess {

	private $domain;
	private $service;
	private $known_services;

	public function __construct($email, $cfg)
	{
		$this->known_services = $cfg;
		$pieces = preg_split('/@/', $email);

		$this->domain = $pieces[1];
		$this->service = null;
	}

	public function isAKnownEmailProvider()
	{
		if ($this->service === null) {
			$this->lookupServices();
		}

		return ($this->service !== false);
	}

	public function getProvider()
	{
		return $this->known_services[$this->service];
	}

	private function lookupServices()
	{
		$found = false;

		foreach ($this->known_services as $i => $known) {
			if (preg_match($known['regexp'], $this->domain)) {
				$this->service = $i;
				$found = true;
				break;
			}
		}

		if (!$found) {
			$this->service = false;
		}
	}

}
