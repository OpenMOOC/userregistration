<?php
/**
 *
 * @param array &$links  The links on the frontpage, split into sections.
 */
function userregistration_hook_frontpage(&$links) {
	assert('is_array($links)');
	assert('array_key_exists("links", $links)');

	$links['auth'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('userregistration/index.php'),
		'text' => '{userregistration:userregistration:link_panel}',
	);
}
