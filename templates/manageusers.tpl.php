<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_panel}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])){ ?>
	  <div class="alert alert-error"><?php echo $this->data['error']; ?></div>
<?php }?>

<h1><?php echo $this->t('link_manageusers'); ?></h1>

<p>
<?php echo $this->t('manage1_search_instructions')?>
</p>
<?php
$used_attr = isset($this->data['attr']) ? $this->data['attr'] : '';
$used_pattern = isset($this->data['pattern']) ? $this->data['pattern'] : '';
?>

<form class="form-inline" id="search_form" method="get">
 <fieldset>
  <select class="input-small" name="attr">
  <option value="mail"<?php echo ($used_attr == 'mail' ? ' selected="selected"' : '')?>><?php echo $this->t('{attributes:attribute_mail}')?></option>
   <option value="cn"<?php echo ($used_attr == 'cn' ? ' selected="selected"' : '')?>><?php echo $this->t('attribute_cn')?></option>
   <option value="sn"<?php echo ($used_attr == 'sn' ? ' selected="selected"' : '')?>><?php echo $this->t('attribute_sn')?></option>
  </select>
<?php echo $this->t('starts_with')?>
  <input name="pattern" class="input-normal" type="text" value="<?php echo $used_pattern?>" />
  <input class="btn" type="submit" id="search_button" name="search" value="<?php echo $this->t('search')?>" />
 </fieldset>
</form>
<?php
if (!empty($used_attr) && !empty($used_pattern)):
?>
<h3><?php echo $this->t('search_results')?></h3>

<p class="pull-right"><?php echo $this->t('filter')?> <span class="label label-info"><?php echo $this->t('{attributes:attribute_' . $used_attr . '}') ?></span> <?php echo $this->t('starts_with')?> <span class="label"><?php echo $used_pattern ?></span></p>
<?php
endif;

$results = isset($this->data['search_results']) ? $this->data['search_results'] : null;
if ($results !== null):

    if (count($results) > 0):
?>
<table id="search_results" class="table table-striped table-hover">
 <thead>
  <tr>
  <th><?php echo $this->t('{attributes:attribute_mail}')?></th>
  <th><?php echo $this->t('attribute_cn')?></th>
  <th><?php echo $this->t('attribute_sn')?></th>
  <th></th>
  </tr>
 </thead>
 <tbody>
<?php
        foreach ($results as $u):
?>
  <tr>
  <td><?php echo $u['mail']?></td>
  <td><?php echo $u['cn']?></td>
  <td><?php echo $u['sn']?></td>
   <td>
   <a class="btn btn-small"><?php echo $this->t('edit')?></a>
   <a class="btn btn-small btn-danger"><?php echo $this->t('remove') ?></a>
   </td>
  </tr>
<?php
        endforeach;
?>
 </tbody>
</table>
<?php
    else:
?>
<div id="search_no_results" class="alert alert-error"><?php echo $this->t('no_results')?></div>
<?php
    endif;
endif;
?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
