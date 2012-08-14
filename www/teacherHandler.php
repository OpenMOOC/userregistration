<?php

$authId = 'admin';
$auth = new SimpleSAML_Auth_Simple($authId);
$auth->requireAuth();

$store = sspmod_userregistration_Storage_UserCatalogue::instantiateStorage();

if (isset($_POST) && isset($_POST['save'])) {
	$oldTeachers = explode(',', $_POST['oldteachers']);
	$teachers2add = array_diff($_POST['teachers'] ,$oldTeachers);
	$teachers2del = array_diff($oldTeachers, $_POST['teachers']);

	foreach($teachers2add as $teacher2add) {
		$store->updateUser($teacher2add, array('eduPersonAffiliation' => array('student','teacher')));
	}
	foreach($teachers2del as $teacher2del) {
		$store->updateUser($teacher2del, array('eduPersonAffiliation' => array('student')));
	}
}


$users = array();
$users['student'] = array();
$users['teacher'] = array();

$attrs = array("mail", "eduPersonAffiliation");

$attrlist = array ('sn' => 'sn',
				   'cn' => 'cn',
				   'mail' => 'mail', 
				   'eduPersonAffiliation' => 'eduPersonAffiliation'
);

if (isset($_POST['search_param']) && !empty($_POST['search_param'])) {
	$searchParam = '*'.$_POST['search_param'].'*';
}
else {
	$searchParam = '*';
}

$usersData = $store->getUsers($attrlist, $searchParam);

$html = new SimpleSAML_XHTML_Template(
	SimpleSAML_Configuration::getInstance(),
	'userregistration:teacherHandler.tpl.php',
	'userregistration:userregistration'
);


$html->data['usersData'] = $usersData;

$html->show();
