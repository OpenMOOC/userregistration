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

// Searchable attributes
$searchable_attributes = array();
foreach ($searchOptions['searchable'] as $attr) {
    $lc_attr = strtolower($attr);
    $searchable_attributes[$attr] = $html->t('{attributes:attribute_' . $lc_attr . '}');
}


if ($search === true) {
    $attr = isset($_GET['attr']) ? $_GET['attr'] : '';
    $pattern = isset($_GET['pattern']) ? trim($_GET['pattern']) : '';

    if (!empty($attr) && !empty($pattern)) {
        $html->data['attr'] = $attr;
        $html->data['pattern'] = $pattern;
    }

    if (!isset($searchable_attributes[$attr])) {
        $html->data['error'] = $html->t('attribute_not_searchable');
    } elseif (isset($searchOptions['min_length']) && strlen($pattern) < $searchOptions['min_length']) {
        $html->data['error'] = $html->t('min_search_length', array('%MIN%' => $searchOptions['min_length']));
    } else {
        $search_filter = preg_replace('/%STRING%/', $pattern, $searchOptions['filter']);
        $search_results = $store->searchUsers($attr, $search_filter);

        // Pagination
        if ($searchOptions['pagination'] === true) {
            $elems = $searchOptions['elems_per_page'];
            // Parameters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

            $paginate = new sspmod_userregistration_Paginate($search_results, $elems);
            $base_url = SimpleSAML_Utilities::selfURL();
            $paginate->setBaseURL($base_url);
            $paginate->setPage($page);
            $search_results = $paginate->getPageElements();
            $html->data['pagination'] = $paginate->getButtons();
        }
    }
}


$html->data['searchable_attributes'] = $searchable_attributes;
$html->data['search_results'] = $search_results;
$html->show();
