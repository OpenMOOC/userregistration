<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_newuser}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])){ ?>
	  <div class="alert alert-error"><?php echo $this->data['error']; ?></div>
<?php }?>

<h1><?php echo $this->t('s1_head', $this->data['systemName']); ?></h1>

<p><?php echo $this->t('s1_para1'); ?></p>

<?php print $this->data['formHtml']; ?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
