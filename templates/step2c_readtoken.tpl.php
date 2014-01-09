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
	  <h1><?php echo $this->t('s1_readtoken_head'); ?></h1>
	  <p><?php echo $this->t('s1_readtoken_info'); ?></p>
</div>


<div style="margin: 1em">
        <form method="POST" action="<?php echo $this->data['url'];?>">
                <table class="formTable">
                        <tr class="element">
                                <td class="labelcontainer">
                                        <label for="token">Token:</label>
                                </td>
                                <td>
                                        <input class="inputelement" type="text" value="" name="token" id="token" size="50">
                                </td>
                        </tr>
                        <tr>
                                <td></td><td><button type="submit" class="btn btn-primary" name="savetoken"><?php echo $this->t('save');?></button></td>
                        </tr>
                </table>
        </form>
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
