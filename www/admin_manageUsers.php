<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$viewAttr = $uregconf->getArray('attributes');
$formFields = $uregconf->getArray('formFields');
$eppnRealm = $uregconf->getString('user.realm');
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);
$searchOptions = $uregconf->getArray('search');

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
$html->data['searchOptions'] = $searchOptions;
$html->data['systemName'] = $systemName;
$html->data['customNavigation'] = $customNavigation;

if ($search === true) {
    $attr = isset($_GET['attr']) ? $_GET['attr'] : '';
    $pattern = isset($_GET['pattern']) ? trim($_GET['pattern']) : '';

    if (!empty($attr) && !empty($pattern)) {
        $html->data['attr'] = $attr;
        $html->data['pattern'] = $pattern;
    }

    if (!isset($searchOptions['min_length']) || strlen($pattern) >= $searchOptions['min_length']) {
        $search_results = $store->searchUsers($attr, $pattern . '*');
    } else {
        $html->data['error'] = $html->t('min_search_length', array('%MIN%' => $searchOptions['min_length']));
    }
}

$html->data['search_results'] = $search_results;
$html->show();
