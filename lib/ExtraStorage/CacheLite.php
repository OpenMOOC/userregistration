<?php
require_once('Cache/Lite.php');

class sspmod_userregistration_ExtraStorage_CacheLite implements sspmod_userregistration_ExtraStorage_IDriver {
	protected $cacheLite;

	public function __construct($config, $expire = 3600)
	{
		$options = array(
			'cacheDir' => $config['cacheDir'],
			'lifeTime' => $expire
		);

		$this->cacheLite = new Cache_Lite($options);
	}

	public function store(sspmod_userregistration_ExtraData_Base $data)
	{
		$key = $this->getPrefixedKey($data);
		$this->cacheLite->save(json_encode($data->getData()), $key);
	}

	public function retrieve($key, $class)
	{
		$obj = new $class($key);
		$get_key = $this->getPrefixedKey($obj);
		$data = $this->cacheLite->get($get_key);

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
		$this->cacheLite->remove($key);
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
