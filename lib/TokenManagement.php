<?php
// Load redis library
require_once __DIR__ . '/../lib/predis-0.8.3/lib/Predis/Autoloader.php';
Predis\Autoloader::register();

class sspmod_userregistration_TokenManagement {

	// 28 bytes long
	const tokenLength = 28;

	protected $lifetime;

	protected $redis;

	public function __construct($config, $lifetime)
	{
		$this->lifetime = $lifetime;
		$this->redis = new \Predis\Client($config);
	}

	// Stores a token on redis
	public function generate($email)
	{
		$token = $this->buildKey();
		$data = array(
			'type' => 'token',
			'email' => $email,
		);
		$this->redis->set($token, json_encode($data));
		$this->redis->expire($token, $this->lifetime);

		return $token;
	}

	public function getDetails($token)
	{
		$data = $this->redis->get($token);

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

	public function delete($key)
	{
		$this->redis->del($key);
	}

	public function addGotoURL($email, $url)
	{
		$key = $email . ':goto';
		$this->redis->set($key, $url);
	}

	public function getGotoURL($email)
	{
		$key = $email . ':goto';
		$data = $this->redis->get($key);
		if ($data === null) {
			return false;
		} else {
			return $data;
		}
	}

	protected function buildKey()
	{
		return bin2hex(openssl_random_pseudo_bytes(self::tokenLength));
	}
}
