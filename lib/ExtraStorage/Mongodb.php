<?php

class sspmod_userregistration_ExtraStorage_Mongodb implements sspmod_userregistration_ExtraStorage_IDriver {
	protected $mongoclient;
    protected $mongodb;
    protected $collection;

	public function __construct($config, $expire = 3600)
	{
		$this->mongoclient = new MongoClient($config['scheme'].'://'.$config['host'].':'.$config['port']);
		$this->mongodb = $this->mongoclient->selectDB($config['database']);
		$this->collections_created = false;

		// Create collections if they don't exist
		foreach (array('tokens', 'gotos', 'misc') as $known_collection) {
			$coll = new MongoCollection($this->mongodb, $known_collection);
			$coll->ensureIndex(array('key' => 1));
			if ($known_collection == 'tokens') {
				$coll->ensureIndex(
					array('timestamp' => 1),
					array('expireAfterSeconds' => $expire)
				);
			}
		}
	}

	public function store(sspmod_userregistration_ExtraData_Base $data)
	{
		$this->selectCollectionByData($data);

		$query = array("key" => $data->getKey());
		$mongo_data = array(
			'key' => $data->getKey(),
			'data' => $data->getData(),
			// Needed for expiration
			'timestamp' => new MongoDate(),
		);
		
		$this->collection->update($query, $mongo_data, array(
			'upsert'=> TRUE,
			'fsync' => TRUE,
			'safe' => TRUE,
		));
	}

	public function retrieve($key, $class)
	{
		$obj = new $class($key);
		$this->selectCollectionByData($obj);
		$query = array('key' => $key);
		$data = $this->collection->findone($query);

		// TODO: Don't retrieve data if expired
		if ($data === null || empty($data) || !is_array($data) || !array_key_exists('data', $data)) {
			return false;
		} else {
			$obj->rebuild($data['data']);
			return $obj;
		}
	}

	public function delete(sspmod_userregistration_ExtraData_Base $data)
	{
		$this->selectCollectionByData($data);
		$query = array("key" => $data->getKey());
		$this->collection->remove($query);
	}

	protected function selectCollectionByData(sspmod_userregistration_ExtraData_Base $data)
	{
		$collection = 'misc';
		if ($data instanceof sspmod_userregistration_ExtraData_AccountCreationToken ||
				$data instanceof sspmod_userregistration_ExtraData_MailChangeToken ||
				$data instanceof sspmod_userregistration_ExtraData_PasswordChangeToken) {
			$collection = 'tokens';
		} elseif ($data instanceof sspmod_userregistration_ExtraData_GotoURL) {
			$collection = 'gotos';
		}

		$this->collection = $this->mongodb->selectCollection($collection);
	}
}

