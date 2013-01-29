<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_review}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])) { ?>
          <div class="alert alert-error"><?php echo $this->data['error']; ?></div>
<?php
      }
 
      if(isset($this->data['success']) && $this->data['success']){

          $logout_link = '<a href="' . $this->data['logout_url'] . '">' . $this->t('log_out').'</a>';
	
 ?>
         <div class="alert alert-info"><?php echo $this->t('message_chuinfo').' '. $logout_link .' '.$this->t('message_chuinfo2');  ?></div>
<?php } 

if (isset($this->data['formHtml'])) {

?>

<h1><?php echo $this->t('review_head'); ?></h1>
<p>
        <?php echo htmlspecialchars($this->t('review_intro', array('%UID%' => '<b>' . $this->data['uid'] . '</b>') ), ENT_QUOTES); echo $this->t('review_intro2'); ?>
</p>

<?php print $this->data['formHtml'];

}
 ?>

<?php 
	if (!$this->data['customNavigation']) {
?>

<h2><?php echo $this->t('new_head_other'); ?></h2>
<ul>
	<li><a href="changePassword.php"><?php echo $this->t('link_changepw'); ?></a></li>
	<li><a href="index.php"><?php echo $this->t('return'); ?></a></li>
	<li><a href="reviewUser.php?logout=true"><?php echo $this->t('{status:logout}'); ?></a></li>
</ul>

<?php
}
?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
