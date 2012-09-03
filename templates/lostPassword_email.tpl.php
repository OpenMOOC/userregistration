<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_lostpw}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])){ ?>
	  <div class="alert alert-error"><?php echo $this->data['error']; ?></div>
<?php }?>

<form method="post" action="lostPassword.php">
<div style="margin: 1em">
	<h1><?php echo $this->t('lpw_head'); ?></h1>

	<p><?php echo $this->t('lpw_para1'); ?></p>

	<table>
		<tr>
		<td><label><?php echo $this->t('{attributes:attribute_mail}'); ?>:</label></td><td>
		<input type="text" size="50" name="emailreg" value="<?php
		if (isset($this->data['email'])) echo htmlspecialchars($this->data['email']);
		?>"/></td></tr>
	</table>

	<p><?php echo $this->t('lpw_para2'); ?></p>

	<p><input type="submit" class="btn" name="save" value="<?php echo $this->t('submit_mail'); ?>" />

</div>
</form>

<?php 
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
