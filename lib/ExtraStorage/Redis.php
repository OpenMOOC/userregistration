<?php

class sspmod_userregistration_ExtraStorage_Redis implements iExtraStorage {
	protected $redis;

	public function __construct($config)
	{    
		$this->redis = new Redis();
        $this->redis->connect($config['scheme'].'://'.$config['host'], $config['port']);
	}

	public function store($data)
	{
		$this->redis->set($data->getKey(), json_encode($data->getData()));
		if ($data->getExpire() !== false) {
			$this->redis->expire($data->getKey(), $data->getExpire());
		}
	}

	public function retrieve($key)
	{
		$data = $this->redis->get($key);

		if ($data === null) {
			return false;
		} else {
			$decoded_data = @json_decode($data, true);
			if (!is_array($decoded_data)) {
				return false;
			} else {
				return new sspmod_userregistration_ExtraData_Base($key, $decoded_data);
			}
		}
	}

	public function delete($key)
	{
		$this->redis->del($key);
	}
}




?>
