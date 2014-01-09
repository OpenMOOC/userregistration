
<h1><?php echo $this->t('mailLost_header', $this->data['systemName']);?></h1>

<p><?php echo $this->t('mailLost_urlintro', $this->data['systemName']);?></p>
<p><tt><a href="<?php echo $this->data['pwResetUrl']; ?>"><?php echo $this->data['pwResetUrl']; ?></a></tt></p>

<p><?php echo $this->t('mail_manualtoken_url');?>:</p>
<p><?php echo $this->data['pwManualResetUrl'];?></p>
<p><?php echo $this->t('mail_manualtoken_token');?>:</p>
<p><?php echo $this->data['tokenValue'];?></p>

<p><?php echo $this->t('mail_tokeninfo', array('%DAYS%' => $this->data['tokenLifetime']/(3600*24)));?></p>

<p><?php echo $this->t('mail1_signature', $this->data['systemName']);?></p>
