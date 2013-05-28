<?php

class sspmod_userregistration_ExtraStorage_Manager {
	private static $instance;

	public static function getInstance()
	{
		if (self::$instance === null) {
			$driver = self::getDriver();
			$config = SimpleSAML_Configuration::getConfig('module_userregistration.php');
			switch($driver) {
				case 'redis':
					self::$instance = new sspmod_userregistration_ExtraStorage_Redis(
						$config['redis']
					);
					break;
				default:
					self::$instance = null;
			}
		}

		return self::$instance;
	}
}
