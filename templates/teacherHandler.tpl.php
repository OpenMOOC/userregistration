<?php

$this->data['header'] = $this->t('{userregistration:userregistration:link_panel}');
$this->data['head'] = '<link rel="stylesheet" href="resources/userregistration.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php');

?>

<form method="POST">

<table>
	<tr>
		<td><label>Filter param:</label></td>
		<td><input type="text" name="search_param" value="<?php echo (isset($_POST['search_param'])? htmlspecialchars($_POST['search_param'], ENT_QUOTES):'') ?>"></td>
		<td><input class="btn" type="submit" name="search" value="Filter"> (Filter if match any mail)</td>
	</tr>
</table>
<br><br>

<table cellspacing="2" class="users">
<tr><th>User</th><th>Teacher?</th></tr>

<?php

$oldteacher = array();
foreach ($this->data['usersData'] as $userData) {
	$teacher = false;
	if(in_array('teacher', $userData['eduPersonAffiliation'])) {
		$teacher = true;
		$oldteacher[] = $userData['mail'][0];
	}
	$sn = htmlspecialchars($userData['sn'][0], ENT_QUOTES);
	$cn = htmlspecialchars($userData['cn'][0], ENT_QUOTES);
    $mail = htmlspecialchars($userData['mail'][0], ENT_QUOTES);
	echo '<tr><td class="label"><label>'.$cn.' '.$sn.' ('.$mail.')</label></td><td class="teacher"><input type="checkbox" name="teachers[]" value="'.$mail.'" '.($teacher? 'checked="checked"':'').'></td></tr>';
}

?>
<tr><td colspan="2" align="center"><input class="btn" type="submit" name="save" value="Save"></td></tr>
</table>

<input type="hidden" name="oldteachers" value="<?php echo implode(',', $oldteacher); ?>">

</form>


<?php
$this->includeAtTemplateBase('includes/footer.php');
?>
