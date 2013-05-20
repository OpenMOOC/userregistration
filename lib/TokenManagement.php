<?php
// Load redis library
require_once __DIR__ . '/../lib/predis-0.8.3/lib/Predis/Autoloader.php';
Predis\Autoloader::register();

class sspmod_userregistration_TokenManagement {

	protected $lifetime;

	protected $redis;

	public function __construct($lifetime)
	{
		$this->lifetime = $lifetime;
		// TODO make this configurable
		$this->redis = new \Predis\Client();
	}

	// Stores a token on redis
	public function store($email, $token)
	{
		$key = $this->buildKeyFromParams($email, $token);
		$data = array(
			'email' => $email,
			'token' => $token,
		);
		$this->redis->set($key, json_encode($data));
		$this->redis->expire($key, $this->lifetime);

		return $sha1token;
	}

	public function getDetails($sha1)
	{
		$key = $this->buildKeyFromSHA1($sha1);
		$data = $this->redis->get($key);

		if ($email === null) {
			return false;
		} else {
			return json_decode($data);
		}
	}

	protected function buildKey($email, $token)
	{
		return 'key:' . sha1($email . ':' . $token);
	}

	protected function buildKeyFromSHA1($sha1, $param)
	{
		return 'key:' . $sha1 . ':' . $param;
	}
}
