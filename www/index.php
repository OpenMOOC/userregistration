<?php

$config = SimpleSAML_Configuration::getInstance();
$session = SimpleSAML_Session::getInstance();
$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
/* Get a reference to our authentication source. */
$asId = $uregconf->getString('auth');

$links = array();



	$links[] = array(
		'href' => SimpleSAML_Module::getModuleURL('userregistration/newUser.php'),
		'text' => '{userregistration:userregistration:link_newuser}',
	);

	$links[] = array(
		'href' => SimpleSAML_Module::getModuleURL('userregistration/lostPassword.php'),
		'text' => '{userregistration:userregistration:link_lostpw}',
	);

	if($session->isAuthenticated()) {
        // Admin links
        $isadmin = SimpleSAML_Utilities::isAdmin();
        if ($isadmin) {
            $admin_links = array();
            $admin_links[] = array(
                'href' => SimpleSAML_Module::getModuleURL('userregistration/admin_newUser.php'),
                'text' => '{userregistration:userregistration:link_newuser}',
            );

            $html->data['admin_links'] = $admin_links;
        }
		$uregconf = SimpleSAML_Configuration::getConfig('module_userregistration.php');
		if($session->getAuthority() == $asId) {
			$as = new SimpleSAML_Auth_Simple($asId);

			$links[] = array(
				'href' => SimpleSAML_Module::getModuleURL('userregistration/reviewUser.php'),
				'text' => '{userregistration:userregistration:link_review}',
			);
			$links[] = array(
				'href' => SimpleSAML_Module::getModuleURL('userregistration/changePassword.php'),
				'text' => '{userregistration:userregistration:link_changepw}',
			);
/*
			$links[] = array(
				'href' => SimpleSAML_Module::getModuleURL('userregistration/delUser.php'),
				'text' => '{userregistration:userregistration:link_deluser}',
			);
*/
			$links[] = array(
				'href' => $as->getLogoutURL(),
				'text' => '{status:logout}',
			);
		} else {
			$links[] = array(
				'href' => SimpleSAML_Module::getModuleURL('userregistration/reviewUser.php'),
				'text' => '{userregistration:userregistration:link_enter}',
            );
		}
	} else {
		$links[] = array(
			'href' => SimpleSAML_Module::getModuleURL('userregistration/reviewUser.php'),
			'text' => '{userregistration:userregistration:link_enter}',
		);
	}

$html = new SimpleSAML_XHTML_Template(
		$config,
		'userregistration:index.tpl.php',
		'userregistration:userregistration');
$html->data['source'] = $asId;
$html->data['links'] = $links;

if(array_key_exists('status', $_GET) && $_GET['status'] == 'deleted') {
	$html->data['userMessage'] = 'message_userdel';
}


$html->show();

