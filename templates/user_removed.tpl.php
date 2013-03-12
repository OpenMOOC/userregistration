<?php

$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<div style="margin: 1em">
  <p><?php echo $this->t('user_removed', array('%USER%' => $this->data['user'])); ?></p>

<a class="btn" href="<?php echo SimpleSAML_Module::getModuleURL('userregistration/admin_manageUsers.php') . '?search=x&attr=' . urlencode($this->data['attr']) . '&pattern=' . urlencode($this->data['pattern']) ?>"><?php echo $this->t('back_to_search')?></a>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
