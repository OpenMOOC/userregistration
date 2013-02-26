<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_panel}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php');

if(isset($this->data['userMessage'])){ ?>
	<div class="alert alert-info"><?php echo $this->t($this->data['userMessage']); ?></div>
<?php }?>

<h1> <?php echo $this->t('{userregistration:userregistration:link_panel}') ?> </h1> 

<ul>
<?php
	foreach ($this->data['links'] as $link) {
		echo '<li><a href="' . htmlspecialchars($link['href'], ENT_QUOTES) . '">' . htmlspecialchars($this->t($link['text']), ENT_QUOTES) . '</a>';
		if(isset($link['extra_text'])) {
			echo $link['extra_text'];
		}
		echo '</li>';
	}
?>
</ul>

<?php if (isset($this->data['admin_links'])): ?>
<h2><?php echo $this->t('{userregistration:userregistration:admin}')?></h2>

<ul>
<?php
	foreach ($this->data['admin_links'] as $link) {
		echo '<li><a href="' . htmlspecialchars($link['href'], ENT_QUOTES) . '">' . htmlspecialchars($this->t($link['text']), ENT_QUOTES) . '</a>';
		if(isset($link['extra_text'])) {
			echo $link['extra_text'];
		}
		echo '</li>';
	}
    endif;
?>
</ul>

<?php
$this->includeAtTemplateBase('includes/footer.php');
?>
