<?php 

$this->data['header'] = $this->t('{userregistration:userregistration:link_newuser}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

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
<a class="btn btn-success" href="<?php echo $this->data['goto']?>">
<?php echo $this->t('first_login_with_goto') ?>
</a>
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
