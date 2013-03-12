<?php

$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<div style="margin: 1em">
  <h1><?php echo $this->t('confirm_remove_head'); ?></h1>
  <p><?php echo $this->t('confirm_remove_par', array('%USER%' => $this->data['user'])); ?></p>

<form method="GET">
<input type="hidden" name="user" value="<?php echo $this->data['user']?>" />
<input type="hidden" name="attr" value="<?php echo $this->data['attr']?>" />
<input type="hidden" name="pattern" value="<?php echo $this->data['pattern']?>" />
<input class="btn btn-danger" type="submit" name="confirm" value="<?php echo $this->t('remove') ?>" />
<a class="btn" href="<?php echo $this->data['return_url'] ?>"><?php echo $this->t('cancel')?></a>
</form>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
