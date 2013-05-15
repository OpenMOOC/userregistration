<?php

class sspmod_userregistration_MailGuesser {

	private $domain;
	private $service;

	public static $known_services = array(
		array(
			'name' => 'GMail',
			'regexp' => '/g(oogle)?mail.com/',
			'url' => 'http://www.gmail.com',
			'image' => 'gmail.png',
		),

		array(
			'name' => 'Outlook',
			'regexp' => '/(hotmail|outlook).com/',
			'url' => 'http://www.outlook.com',
			'image' => 'outlook.png',
		),
	);

	public function __construct($email)
	{
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
		return self::$known_services[$this->service];
	}

	private function lookupServices()
	{
		$found = false;

		foreach (self::$known_services as $i => $known) {
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
