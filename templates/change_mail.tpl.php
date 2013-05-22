<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_changemail}');

$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<script type="text/javascript" src="resources/jquery-simplePassMeter/jquery.js"></script>

<?php if(isset($this->data['error'])){ ?>
	<div class="alert alert-error"><?php echo $this->data['error']; ?></div>
<?php }?>
<?php if(isset($this->data['userError'])){ ?>
	<div class="alert alert-error"><?php echo $this->t($this->data['userError']); ?></div>
<?php }?>
<?php if(isset($this->data['userMessage'])){ ?>
	<div class="alert alert-info">
<?php 
    echo $this->t($this->data['userMessage']); 
    if (isset($this->data['reLoginMessage'])) {
        echo '<br>'.$this->t($this->data['reLoginMessage']);
    }
?>

    </div>
<?php }

if (isset($this->data['formHtml'])) {

?>

<h1><?php echo $this->t('cm_head'); ?></h1>
<p><?php echo $this->t('cm_para1', array('%UID%' => $this->data['uid']) ); ?></p>


<?php echo $this->data['formHtml'];

}
 ?>

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
