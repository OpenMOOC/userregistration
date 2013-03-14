<?php

   /*
	* Function to obtain a dn from basedn and entry object (user) values
	*
	*  Used in lib/Storage/UserCatalogue.php
	*
	* @param	object	$localconfig	Local ldap source configuration params (from config/authsources.php)
	* @param	object	$userinfo		User values.
	* @return	string	$dn				ldap unique name
	*/
	function get_dn_hook($localconfig, $registerconfig, $userinfo) {
		$base = $localconfig->getString('search.base');
		$user_id_param = $registerconfig->getString('user.id.param', 'uid');
		$id =  $userinfo[$user_id_param];
		if(is_array($id)) {
			$id = $id[0];
		}
		$rdn = $user_id_param.'='.$id;
		$dn  = $rdn.','.$base;
		return $dn;
	}

   /*
	* Function to obtain a dn from basedn and entry object (user) values
	*
	*  Used in lib/Util.php
	*
	* @param	object	$userinfo		User values
	* @return	string	$cn				ldap common name
	*/

	function get_cn_hook($userinfo) {
		$givenName = (isset($userinfo['givenName'])? $userinfo['givenName'] :'');
		if(!isset($userinfo['cn']) || empty($userinfo['cn']))
		{	$sn = $userinfo['sn'];
			$cn = $givenName.' '.$sn;
		}
		else {
			$cn = $userinfo['cn'];
		}
		return $cn;
	}


	// For new registration, should also work for updated information
	function processInput($fieldValues, $wanted, $attributeDefinitions){

		$skv = array();

		foreach($wanted as $field){
			if ($field == 'pw1' || $field == 'pw2') {
				$db = 'userPassword';
			} else {
				$db = $attributeDefinitions[$field];
			}

			switch($db){
				case "cn":
					$skv[$db] = get_cn_hook($fieldValues);
					break;
				case "userPassword":
					$skv[$db] = sspmod_userregistration_Util::validatePassword($fieldValues);
					break;
				default:
					if (isset($fieldValues[$field])) {
						$skv[$db] = $fieldValues[$field];
					}
			}
		}
		return $skv;
	}


	// Filter attributes 
	function filterAsAttributes($asAttributes, $reviewAttr, $attributeDefinitions){
		$attr = array();

		foreach($reviewAttr as $fieldName){
			$attrName = $attributeDefinitions[$fieldName];
			switch($attrName){
			case "userPassword":
				break;
			default:
				if(array_key_exists($attrName, $asAttributes)){
					$attr[$fieldName] = $asAttributes[$attrName][0];
				}
			}
		}
		return $attr;
	}



?>
