<?php

interface iExtraStorage {

	public function store($data);
	public function retrieve($key);
	public function delete($key);
}


class sspmod_userregistration_ExtraStorage {
    
	public static function instantiateExtraStorage()
	{
        $extraStoreSel = self::getStorageSelection();

        $extraStorageConfig = self::getSelectedStorageConfig();
      
        if ($extraStoreSel == 'redis') {
            return new sspmod_userregistration_ExtraStorage_Redis($extraStorageConfig);
        }
        else if ($extraStoreSel == 'mongodb') {
            return new sspmod_userregistration_ExtraStorage_Mongodb($extraStorageConfig);
        }
    }
    
    public static function getStorageSelection()
    {
		$rc = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		$extraStoreSel = $rc->getString('extraStorage.backend');
		return $extraStoreSel;
    }

	public static function getSelectedStorageConfig() {
		$extraStoreSel = self::getStorageSelection();
		$rc = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		if($extraStoreSel == 'redis') {
			return $rc->getArray('redis');
		} elseif ($extraStoreSel == 'mongodb') {
            return $rc->getArray('mongodb');
		}
    }   

}
