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
<h1><?php echo $this->t('lpw_head'); ?></h1>
<p><?php echo $this->t('lpw_reg_para1', array('%UID%' => $this->data['uid']) ); ?></p>

<?php
if(isset($this->data['passwordPolicy'])) {
	include_once($this->data['passwordPolicytpl']);
}
?>

<?php print $this->data['formHtml']; ?>

<?php 
	if (!$this->data['customNavigation']) {
?>

<h2><?php echo $this->t('new_head_other'); ?></h2>
<ul>
	<li><a href="index.php"><?php echo $this->t('return'); ?></a></li>
</ul>

<?php
}
?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
