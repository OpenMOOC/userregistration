<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_lostpw}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<div style="margin: 1em">
	  <h1><?php echo $this->t('lpw_success_head'); ?></h1>
	  <p><?php echo $this->t('lpw_success_para1'); ?></p>
</div>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
