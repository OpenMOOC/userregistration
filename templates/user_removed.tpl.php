<?php

$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<div style="margin: 1em">
  <p><?php echo $this->t('users_removed'); ?></p>
<ul>
<?php foreach ($this->data['user'] as $user): ?>
 <li><?php echo $user ?></li>
<?php endforeach; ?>
</ul>

<a class="btn" href="<?php echo $this->data['return_url'] ?>"><?php echo $this->t('back_to_search')?></a>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
