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
		$key = $this->buildKey($email, $token);
		$data = array(
			'type' => 'token',
			'email' => $email,
			'token' => $token,
		);
		$this->redis->set($key, json_encode($data));
		$this->redis->expire($key, $this->lifetime);

		return $key;
	}

	public function getDetails($key)
	{
		$data = $this->redis->get($key);

		if ($data === null) {
			return false;
		} else {
			$decoded_data = @json_decode($data, true);
			if (!is_array($decoded_data)
				|| !isset($decoded_data['type'])
				|| $decoded_data['type'] != 'token') {
				return false;
			} else {
				return $decoded_data;
			}
		}
	}

	protected function buildKey($email, $token)
	{
		return sha1($email . ':' . $token);
	}
}
