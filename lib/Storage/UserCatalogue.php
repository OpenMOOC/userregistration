<?php

interface iUserCatalogue {

	public function addUser($userInfo);
	public function updateUser($userId, $userInfo);
	public function changeUserPassword($userId, $newPlainPassword);
	public function isRegistered($searchKeyName, $value);
	public function isValidPassword($userId, $plainPassword);
	// Exception for no or several users found
	public function findAndGetUser($searchKeyName, $value);
	//public function delUser($userId);
}



/**
 * User catalogue object factory
 *
 * This class is aware of configuration files and will use them.
 */
class sspmod_userregistration_Storage_UserCatalogue {

	public static function instantiateStorage() {
		$selStorage = self::getStorageSelection();
		if($selStorage == 'LdapMod') {
			return self::instantiateLdapStorage();
		} elseif($selStorage == 'AwsSimpleDb') {
		}
	}


	private static function getStorageSelection() {
		$rc = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		$storeSel = $rc->getString('storage.backend');
		return $storeSel;
	}


	public static function getSelectedStorageConfig() {
		// FIXME: In config file. Use same name for conf backend array as storage.backend value
		$selStorage = self::getStorageSelection();
		$selfRegConf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		if($selStorage == 'LdapMod') {
			return $selfRegConf->getArray('ldap');
		} elseif($selStorage == 'AwsSimpleDb') {
		}
	}



	private static function instantiateLdapStorage() {
		$selfRegConf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		$writeConf = $selfRegConf->getArray('ldap');

		$auth = $selfRegConf->getString('auth');
		$authsources = SimpleSAML_Configuration::getConfig('authsources.php');
		$authConf = $authsources->getArray($auth);

		$attributes = $selfRegConf->getArray('attributes');
		$ldap = new sspmod_userregistration_Storage_LdapMod($authConf, $writeConf, $attributes);

		return $ldap;
	}

}

?>
