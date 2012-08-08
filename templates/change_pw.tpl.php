<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_changepw}');

$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';
$this->data['head'] .= '<link rel="stylesheet" href="resources/jquery-simplePassMeter/simplePassMeter.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<script type="text/javascript" src="resources/jquery-simplePassMeter/jquery.js"></script>
<script type="text/javascript" src="resources/jquery-simplePassMeter/jquery.simplePassMeter-0.2b.js"></script>

<?php if(isset($this->data['error'])){ ?>
	<div class="error"><?php echo $this->data['error']; ?></div>
<?php }?>
<?php if(isset($this->data['userMessage'])){ ?>
	<div class="umesg"><?php echo $this->t($this->data['userMessage']); ?></div>
<?php }?>

<h1><?php echo $this->t('cpw_head'); ?></h1>
<p><?php echo $this->t('cpw_para1', array('%UID%' => $this->data['uid']) ); ?></p>


<?php

if(isset($this->data['passwordPolicy'])) {
	include_once($this->data['passwordPolicytpl']);
}
?>


<?php echo $this->data['formHtml']; ?>

<?php 
	if (!$this->data['customNavigation']) {
?>

<h2><?php echo $this->t('new_head_other'); ?></h2>
<ul>
<li><a href="reviewUser.php"><?php echo $this->t('link_review'); ?></a></li>
<li><a href="index.php"><?php echo $this->t('return'); ?></a></li>
<li><a href="changePassword.php?logout=true"><?php echo $this->t('{status:logout}'); ?></a></li>
</ul>

<?php
}
?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
