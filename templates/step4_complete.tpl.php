<?php 

$this->data['header'] = $this->t('{userregistration:userregistration:link_newuser}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';
if (isset($this->data['goto'])) {
	$this->data['head'] .= '<meta http-equiv="refresh" content="5; url='.$this->data['goto'].'">';
}

$this->includeAtTemplateBase('includes/header.php');

if (isset($this->data['stepsHtml'])) {
	echo $this->data['stepsHtml'];
}

?>

<div style="margin: 1em">
  <h1><?php echo $this->t('new_complete_head'); ?></h1>
  <p><?php echo $this->t('new_complete_para1', $this->data['systemName']); ?></p>

<?php
if (isset($this->data['goto'])):
?>
<div class="alert alert-success">
<?php echo $this->t('redirect_to_course') ?>
</div>
<?php
endif;
	if (!$this->data['customNavigation']) {
?>

  <ul>
    <li><a href="reviewUser.php"><?php echo $this->t('link_review'); ?></a></li>
    <li><a href="lostPassword.php"><?php echo $this->t('link_lostpw'); ?></li>
    <li><a href="changePassword.php"><?php echo $this->t('link_changepw'); ?></li>
  </ul>

<?php
}
?>

</div>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
