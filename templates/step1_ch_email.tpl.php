<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_changemail}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php');

?>

<h1><?php echo $this->t('s1_ch_head', $this->data['systemName']); ?></h1>

<?php if (!isset($this->data['admin']) || $this->data['admin'] !== true): ?>
<p><?php echo $this->t('s1_ch_para1'); ?></p>
<?php endif;

if(isset($this->data['error'])): ?>
<div class="alert alert-error">
<?php echo $this->data['error']; ?>
</div>
<?php endif; ?>

<?php
if (isset($this->data['refreshtoken'])):
?>
<div class="alert alert-info">
<?php echo $this->t('didnt_receive_verification_email') ?>
<form action="changeMail.php" method="POST">
 <input type="hidden" name="newmail" value="<?php echo htmlspecialchars($this->data['newmail'], ENT_QUOTES)?>" />
 <input type="submit" name="refreshtoken" value="<?php echo $this->t('get_token')?>" />
</form>
</div>
<?php
endif;
?>

<?php
    if(isset($this->data['formHtml'])) {
        print $this->data['formHtml']; 
    }
?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
