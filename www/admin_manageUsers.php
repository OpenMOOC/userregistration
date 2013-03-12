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

$search = isset($_GET['search']);
$search_results = null;

$html = new SimpleSAML_XHTML_Template(
    $config,
    'userregistration:manageusers.tpl.php',
    'userregistration:userregistration');
$html->data['systemName'] = $systemName;
$html->data['customNavigation'] = $customNavigation;

if ($search === true) {
    $attr = isset($_GET['attr']) ? $_GET['attr'] : '';
    $pattern = isset($_GET['pattern']) ? trim($_GET['pattern']) : '';

    if (!empty($attr) && !empty($pattern)) {
        $html->data['attr'] = $attr;
        $html->data['pattern'] = $pattern;

        $search_results = $store->searchUsers($attr, $pattern . '*');
    }
}

$html->data['search_results'] = $search_results;
$html->show();
