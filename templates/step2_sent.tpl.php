<?php 

$this->data['header'] = $this->t('{userregistration:userregistration:link_newuser}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php
if (isset($this->data['stepsHtml'])) {
	echo $this->data['stepsHtml'];
}
?>

<div style="margin: 1em">
	  <h1><?php echo $this->t('s1_sent_head', $this->data['systemName']); ?></h1>
	  <p><?php echo htmlspecialchars($this->t('s1_para2', array('%MAIL%' => $this->data['email'])), ENT_QUOTES); ?></p>
</div>

<?php

if (isset($this->data['emailProvider'])) {
	$provider = $this->data['emailProvider'];
?>
	<div class="alert alert-info">
	<p><?php echo $this->t('known_provider_detected', array('%PROVIDER%' => $provider['name']))?></p>

<div class="gotoinbox">
	<a href="<?php echo $provider['url'] ?>" class="btn">
	<img style="display: inline" src="resources/emailproviders/<?php echo $provider['image'] ?>" /> <?php echo $this->t('go_to_inbox') ?>
    </a>
</div>
	</div>
<?php
}

	if (!$this->data['customNavigation']) {
?>

<p>
<ul>
	<li><a href="index.php"><?php echo $this->t('return'); ?></a></li>
</ul>
</p>

<?php
}
?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
