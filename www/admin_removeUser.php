<?php

$config = SimpleSAML_Configuration::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
$viewAttr = $uregconf->getArray('attributes');
$formFields = $uregconf->getArray('formFields');
$eppnRealm = $uregconf->getString('user.realm');
$customNavigation = $uregconf->getBoolean('custom.navigation', TRUE);

/* Get a reference to our authentication source. */
$asId = $uregconf->getString('admin.auth');
$as = new SimpleSAML_Auth_Simple($asId);
$as->requireAuth();

$systemName = array('%SNAME%' => $uregconf->getString('system.name') );
$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

$user = isset($_POST['user']) ? $_POST['user'] : '';
$attr = isset($_POST['attr']) ? $_POST['attr'] : '';
$pattern = isset($_POST['pattern']) ? $_POST['pattern'] : '';

if (!is_array($user) || empty($user)) {
    throw new sspmod_userregistration_Error_UserException(
        'void_value',
        'user',
        '',
        'Missing parameter user'
    );
} else {
    foreach ($user as $u) {
        if (!$store->isRegistered($store->userIdAttr, $u)) {
            throw new sspmod_userregistration_Error_UserException(
                'email_not_found',
                $user,
                '',
                'User ' . $user . ' is not registered!'
            );
        }
    }

    $confirm = isset($_POST['confirm']);
    if ($confirm === false) {
        $html = new SimpleSAML_XHTML_Template(
            $config,
            'userregistration:confirm_remove.tpl.php',
            'userregistration:userregistration');
    } else {
        foreach ($user as $u) {
            $store->delUser($u);
        }
        $html = new SimpleSAML_XHTML_Template(
            $config,
            'userregistration:user_removed.tpl.php',
            'userregistration:userregistration');
    }

    $html->data['return_url'] = SimpleSAML_Utilities::addURLparameter(
        SimpleSAML_Module::getModuleURL('userregistration/admin_manageUsers.php'),
        array(
            'search' => '',
            'attr' => $attr,
            'pattern' => $pattern,
        )
    );

    $html->data['user'] = $user;
    $html->data['attr'] = $attr;
    $html->data['pattern'] = $pattern;
    $html->data['customNavigation'] = $customNavigation;

    $html->show();
}

