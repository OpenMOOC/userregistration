<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_newuser}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])): ?>
<div class="alert alert-error">
<?php echo $this->data['error']; ?>
</div>
<?php endif; ?>

<?php
if (isset($this->data['refreshtoken'])):
?>
<div class="alert alert-info">
<?php echo $this->t('didnt_receive_verification_email') ?>
<form method="POST">
 <input type="hidden" name="email" value="<?php echo htmlspecialchars($this->data['email'], ENT_QUOTES)?>" />
 <input type="submit" name="refreshtoken" value="<?php echo $this->t('get_token')?>" />
</form>
</div>
<?php
endif;
?>

<h1><?php echo $this->t('s1_head', $this->data['systemName']); ?></h1>

<?php if (!isset($this->data['admin']) || $this->data['admin'] !== true): ?>
<p><?php echo $this->t('s1_para1'); ?></p>
<?php endif; ?>

<?php print $this->data['formHtml']; ?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
