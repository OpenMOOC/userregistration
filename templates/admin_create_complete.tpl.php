<?php 

$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<div style="margin: 1em">
  <h1><?php echo $this->t('new_complete_head'); ?></h1>
  <p><?php echo $this->t(
      'new_complete_para1_admin',
      array(
          '%USERID%' => $this->data['userid'],
          '%SNAME%' => $this->data['systemName'],
      )
    ); ?></p>

<?php 

    if (isset($this->data['mail_sent']) && $this->data['mail_sent'] === true):
?>
<p><?php echo $this->t(
    'account_details_sent',
    array(
        '%EMAIL%' => $this->data['email'],
    )
);?></p>
<?php
    endif;
?>
<ul>
<li><a href="admin_newUser.php"><?php echo $this->t('link_newuser')?></a>
<li><a href="admin_manageUsers.php"><?php echo $this->t('link_manageusers')?></a>
</ul>
<?php


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
