<?php

class sspmod_userregistration_ExtraStorage_Manager {
	private static $instance;

	public static function getInstance()
	{
		if (self::$instance === null) {
			$config = SimpleSAML_Configuration::getConfig('module_userregistration.php');
			$driver = $config->getstring('extrastorage.driver');
			switch($driver) {
				case 'redis':
					self::$instance = new sspmod_userregistration_ExtraStorage_Redis(
						$config->getArray('redis')
					);
					break;
				default:
					self::$instance = null;
			}
		}

		return self::$instance;
	}
}
