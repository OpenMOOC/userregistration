<?php

class sspmod_userregistration_ExtraStorage_Redis implements sspmod_userregistration_ExtraStorage_IDriver {
	protected $redis;

	public function __construct($config)
	{
		$this->redis = new Redis();
        $this->redis->connect($config['scheme'].'://'.$config['host'], $config['port']);
	}

	public function store(sspmod_userregistration_ExtraData_Base $data)
	{
		$key = $this->getPrefixedKey($data);
		$this->redis->set($key, json_encode($data->getData()));
		if ($data->getExpire() !== false) {
			$this->redis->expire($key, $data->getExpire());
		}
	}

	public function retrieve($key, $class)
	{
		$obj = new $class($key);
		$get_key = $this->getPrefixedKey($obj);
		$data = $this->redis->get($get_key);

		if ($data === null) {
			return false;
		} else {
			$decoded_data = @json_decode($data, true);
			if (!is_array($decoded_data)) {
				return false;
			} else {
				$obj->rebuild($decoded_data);
				return $obj;
			}
		}
	}

	public function delete(sspmod_userregistration_ExtraData_Base $data)
	{
		$key = $this->getPrefixedKey($data);
		$this->redis->del($key);
	}

	private function getPrefixedKey(sspmod_userregistration_ExtraData_Base $data)
	{
		$key = $data->getKey();
		if ($data instanceof sspmod_userregistration_ExtraData_AccountCreationToken ||
				$data instanceof sspmod_userregistration_ExtraData_MailChangeToken ||
				$data instanceof sspmod_userregistration_ExtraData_PasswordChangeToken) {
			return 'token:' . $key;
		} elseif ($data instanceof sspmod_userregistration_ExtraData_GotoURL) {
			return 'goto:' . $key;
		} else {
			// Unknown type
			return $key;
		}
	}
}
