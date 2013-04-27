<h1><?php echo $this->t('mailModified_admin_header', array('%SNAME%' => $this->data['systemName']));?></h1>

<p><?php echo $this->t('mailModified_admin_intro', array('%SNAME%' => $this->data['systemName']));?></p>
<ul>
 <li><?php echo $this->data['userid_translated']?>: <tt><?php echo $this->data['userid']; ?></tt></li>
 <li><?php echo $this->data['password_translated']?>: <tt><?php echo $this->data['password']; ?></tt></li>
</ul>

<p><?php echo $this->t('mail1_signature', array('%SNAME%' => $this->data['systemName']));?></p>
