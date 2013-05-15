<?php

$this->data['header'] = $this->t('{userregistration:userregistration:modifying_user}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';
$this->data['head'] .= '<link rel="stylesheet" href="resources/jquery-simplePassMeter/simplePassMeter.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])) { ?>
          <div class="alert alert-error"><?php echo $this->data['error']; ?></div>
<?php
      }
?>
<script type="text/javascript" src="resources/jquery-simplePassMeter/jquery.js"></script>
<script type="text/javascript" src="resources/jquery-simplePassMeter/jquery.simplePassMeter-0.2b.js"></script>
<script type="text/javascript" src="resources/userregistration.js"></script>
<?php
if (isset($this->data['formHtml'])) {

?>

<h1><?php echo $this->t('modifying_user'); ?></h1>
<p>
<?php
    echo $this->t('admin_review_intro',
            array('%UID%' => '<b>' . htmlspecialchars($this->data['uid'], ENT_QUOTES) . '</b>')
        );
    echo $this->t('admin_review_intro2');
?>
</p>

<?php
if(isset($this->data['passwordPolicy'])) {
	include_once($this->data['passwordPolicytpl']);
}
print $this->data['formHtml'];

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
