<?php

class sspmod_userregistration_ExtraStorage_Mongodb implements iExtraStorage {
	protected $mongoclient;
    protected $mongodb;
    protected $collection;

	public function __construct($config)
	{    
		$this->mongoclient = new MongoClient($config['scheme'].'://'.$config['host'].':'.$config['port']);
        $this->mongodb = $this->mongoclient->selectDB($config['database']);
        try {
            $this->collection = $this->mongodb->selectCollection("userdata");
        }
        catch (Exception $e) {
            $this->collection = new MongoCollection($this->mongodb, "userdata");
            $this->collection->ensureIndex(array('key' => 1));
        }
	}

	public function store($data)
	{
        $query = array("key" => $data->getKey());
        $mongo_data = array("key" => $data->getKey(), "data" => $data->getData(), "expire" => $data->getExpire());
   		
        $this->collection->update($query, $mongo_data, array('upsert'=> TRUE, 'fsync' => TRUE, "safe" => TRUE));
	}

	public function retrieve($key)
	{
        $query = array('key' => $key);
		$data = $this->collection->findone($query);

        # TODO: Don't retrieve data if expired
		if ($data === null || empty($data)) {
			return false;
		} else {
			return new sspmod_userregistration_ExtraData_Base($key, $data['data']);
		}
	}

	public function delete($key)
	{
        $query = array("key" => $key);
		$this->collection->remove($query);
	}
}




?>
