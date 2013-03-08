<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_panel}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])){ ?>
	  <div class="alert alert-error"><?php echo $this->data['error']; ?></div>
<?php }?>

<h1><?php echo $this->t('link_manageusers'); ?></h1>

<form class="form-inline" id="search_form" method="post">
 <fieldset>
  <select class="input-small" name="attr">
   <option name="mail">Email</option>
   <option name="cn"><?php echo $this->t('attribute_cn')?></option>
   <option name="sn"><?php echo $this->t('attribute_sn')?></option>
  </select>
  <input class="input-normal" type="text" placeholder="<?php echo $this->t('starts_with')?>" />
  <a class="btn" href="#" id="search_button"><?php echo $this->t('search')?></a>
 </fieldset>
</form>

<div id="search_no_results" class="alert alert-error"><?php echo $this->t('no_results')?></div>

<table id="search_results" class="table table-striped table-hover">
 <thead>
  <tr>
  <th>Email</th>
  <th><?php echo $this->t('attribute_cn')?></th>
  <th><?php echo $this->t('attribute_sn')?></th>
  <th></th>
  </tr>
 </thead>
 <tbody>
  <tr>
   <td>email@email.xxx</td>
   <td>Jorge</td>
   <td>López</td>
   <td>
   <a class="btn"><?php echo $this->t('edit')?></a>
   <a class="btn btn-danger"><?php echo $this->t('remove') ?></a>
   </td>
  </tr>
 </tbody>
</table>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
