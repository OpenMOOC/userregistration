<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_newuser}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';
if (isset($this->data['admin']) && $this->data['admin'] === true) {
	$this->data['head'] .= '<link rel="stylesheet" href="resources/jquery-simplePassMeter/simplePassMeter.css" type="text/css">';
}

$this->includeAtTemplateBase('includes/header.php');

if (isset($this->data['admin']) && $this->data['admin'] === true):
?>
<script type="text/javascript" src="resources/jquery-simplePassMeter/jquery.js"></script>
<script type="text/javascript" src="resources/jquery-simplePassMeter/jquery.simplePassMeter-0.2b.js"></script>
<?php
endif;

if (isset($this->data['stepsHtml'])) {
	echo $this->data['stepsHtml'];
}

?>

<h1><?php echo $this->t('s1_head', $this->data['systemName']); ?></h1>

<?php
if(isset($this->data['error'])): ?>
<div class="alert alert-error">
<?php echo $this->data['error']; ?>
</div>
<?php
endif;

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

if (isset($this->data['url_lostpassword'])):
?>
<div class="alert alert-info">
<p><?php echo $this->t('lost_my_password_para') ?></p>
<a class="btn btn-info" href="<?php echo $this->data['url_lostpassword']?>"><?php echo $this->t('link_lostpw') ?></a>
</div>
<?php
endif;


if (!isset($this->data['admin']) || $this->data['admin'] !== true): ?>
<p><?php echo $this->t('s1_para1'); ?></p>
<?php endif; ?>

<?php print $this->data['formHtml']; ?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
