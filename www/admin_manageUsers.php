<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$viewAttr = $uregconf->getArray('attributes');
$adminViewAttr = $uregconf->getArray('admin.additional_attributes');
$adminViewAttr = array_merge($viewAttr, $adminViewAttr);
$formFields = $uregconf->getArray('formFields');
$eppnRealm = $uregconf->getString('user.realm');
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);

/* Get a reference to our authentication source. */
$asId = $uregconf->getString('admin.auth');
$as = new SimpleSAML_Auth_Simple($asId);
$as->requireAuth();

$systemName = array('%SNAME%' => $uregconf->getString('system.name') );
$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

$html = new SimpleSAML_XHTML_Template(
    $config,
    'userregistration:manageusers.tpl.php',
    'userregistration:userregistration');
$html->data['systemName'] = $systemName;
$html->data['customNavigation'] = $customNavigation;
$html->data['admin'] = true;
$html->show();
