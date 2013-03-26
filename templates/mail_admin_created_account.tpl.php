<h1><?php echo $this->t('mailNew_admin_header', $this->data['systemName']);?></h1>

<p><?php echo $this->t('mailNew_admin_intro', $this->data['systemName']);?></p>
<ul>
 <li>Nombre de usuario: <tt><?php echo $this->data['email']; ?></tt></li>
 <li>Contraseña: <tt><?php echo $this->data['email']; ?></tt></li>
</ul>

<p><?php echo $this->t('mail1_signature', $this->data['systemName']);?></p>
