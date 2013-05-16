<?php 

$this->data['header'] = $this->t('{userregistration:userregistration:link_changemail}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<div style="margin: 1em">
	  <h1><?php echo $this->t('s1_ch_head', $this->data['systemName']); ?></h1>
	  <p><?php echo htmlspecialchars($this->t('s1_ch_para2', array('%MAIL%' => $this->data['newmail'])), ENT_QUOTES); ?></p>
</div>


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
