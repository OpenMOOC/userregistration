<?php 

$this->data['header'] = $this->t('{userregistration:userregistration:link_changepw}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<div style="margin: 1em">
 	  <div class="alert alert-info"><?php echo $this->t('lpw_complete_para1') ?></div>

<?php
        if (!$this->data['customNavigation']) {
?>
	  <p>
	      <li><a href="index.php"><?php echo $this->t('return'); ?></a></li>
	  </ul>
	  </p>
<?php
}
?>

</div>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
