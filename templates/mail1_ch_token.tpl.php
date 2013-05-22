<h1><?php echo $this->t('mailNew_header', $this->data['systemName']);?></h1>

<p><?php echo $this->t('mailNew_mailintro', $this->data['systemName']);?></p>
<p><tt><?php echo $this->data['newmail']; ?></tt></p>

<p><?php echo $this->t('mailChange_urlintro', $this->data['systemName']);?></p>
<p><tt><a href="<?php echo $this->data['changemailurl']; ?>"><?php echo $this->data['changemailurl']; ?></a></tt></p>

<p><?php echo $this->t('mail_tokeninfo', array('%DAYS%' => $this->data['tokenLifetime']/(3600*24)));?></p>

<p><?php echo $this->t('mail1_signature', $this->data['systemName']);?></p>
