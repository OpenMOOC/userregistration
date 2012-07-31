<?php

   /*
	* Function to obtain a dn from basedn and entry object (user) values
	*
	*  Used in lib/Storage/UserCatalogue.php
	*
	* @param  	object	$localconfig	Local ldap source configuration params (from config/authsources.php)
	* @param  	object	$userinfo		User values.
	* @return 	string  $dn				ldap unique name
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
	* @param  	object	$userinfo		User values
	* @return 	string  $cn				ldap common name
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


	// Generate user edition form fields
	function genFieldView($viewAttr){
		$fields = array();
		foreach($viewAttr as $attrName => $fieldName){
			switch($attrName){
			case "userPassword":
				$fields[] = 'pw1';
				$fields[] = 'pw2';
				break;
			default:
				$fields[] = $fieldName;
			}
		}
		return $fields;
	}


	// For new registration, should also work for updated information
	function processInput($fieldValues, $expectedValues){

		$skv = array();

		foreach($expectedValues as $db => $field){
			switch($db){
			case "cn":
				$hookfile = SimpleSAML_Module::getModuleDir('userregistration') . '/hooks/hook_attributes.php';
				include_once($hookfile);
				$skv[$db] = get_cn_hook($fieldValues);
				break;
			case "userPassword":
				$skv[$db] = sspmod_userregistration_Util::validatePassword($fieldValues);
				break;
			default:
				$skv[$db] = $fieldValues[$field];
			}
		}
		return $skv;
	}


	// Filter attributes 
	function filterAsAttributes($asAttributes, $reviewAttr){
		$attr = array();

		foreach($reviewAttr as $attrName => $fieldName){
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
