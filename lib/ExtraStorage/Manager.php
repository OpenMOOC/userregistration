<?php

class sspmod_userregistration_ExtraStorage_Manager {
	private static $instance;

	public static function getInstance()
	{
		// Expiration time
		$config = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		$mailoptions = $config->getArray('mail');
		$expire = $mailoptions['token.lifetime'];

		if (self::$instance === null) {
			$driver = $config->getstring('extraStorage.backend');
			switch($driver) {
				case 'redis':
					self::$instance = new sspmod_userregistration_ExtraStorage_Redis(
						$config->getArray('redis')
					);
					break;
				case 'mongodb':
					self::$instance = new sspmod_userregistration_ExtraStorage_Mongodb(
						$config->getArray('mongodb'),
						$expire
					);
					break;
				default:
					self::$instance = null;
			}
		}

		return self::$instance;
	}
}
