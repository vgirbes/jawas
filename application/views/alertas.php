	<div id="status-save" class="blink_me"></div>
	<div id="izq-mails-back"></div>
	<div id="izq-mails"<?= (isset($contacts) ? ' style="overflow-y:scroll;"' : '');?>>
		<?php if (isset($contacts)){ 
			foreach ($contacts as $emails){
				echo '<div class="mail-contact" onclick="save_mail_google($(this));" title="'.$emails.'" id="email_'.time().'">'."\n";
				echo $emails.'<br/>';
				echo '</div>'."\n";
			}?>
			<input type="hidden" name="url" id="url" value="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/alerts/<?= $lista_tipo;?>">
			<input type="hidden" name="type" id="type" value="<?= $lista_tipo;?>">
		<?php }else{ ?>
		<div class="import-mails">
			<span class="title-mails">
				<?= lang('alerts.lista_titulo');?> <?= $lista_tipo;?><br/><br/>
				<a href="https://accounts.google.com/o/oauth2/auth?client_id=<?= CLIENT_ID;?>&redirect_uri=http://localhost/es/administration/alerts&scope=https://www.google.com/m8/feeds/&response_type=code" class="boton btn-alert"><?= lang('alerts.importar_contacto');?></a><br/><br/>
				<form method="post" id="f_alertas" action="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/alerts/<?= $lista_tipo;?>">
					<input class="input_type" required="" type="text" placeholder="Email" name="email" id="email" value="">
					<br/>
					<a class="boton btn-alert" href="javascript:save_mail();"><?= lang('alerts.add_contact');?></a><br/><br/>
					<input type="hidden" name="url" id="url" value="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/alerts/<?= $lista_tipo;?>">
					<input type="hidden" name="type" id="type" value="<?= $lista_tipo;?>">
				</form>
			</span>
		</div>
		<?php } ?>
	</div>
	<div id="der-mails-back"></div>
	<div id="der-mails">
		<?php 
		if (isset($lista_emails) && ($lista_emails)){
			foreach ($lista_emails->result() as $row){
				echo '<div class="mail-contact" onclick="delete_mail($(this));" title="'.$row->email.'" id="email_'.$row->id.'">'."\n";
				echo $row->email.'<br/>';
				echo '</div>'."\n";
			}
		}else{ ?>
			<div class="import-mails">
				<span class="title-mails">
			<?php
			echo lang('alerts.sin_contactos');
			?>
				</span>
			<input type="hidden" id="hay" name="hay" value="no">
			</div>
			<?php
		}
		?>
	</div>
	<input type="hidden" name="url_delete" id="url_delete" value="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/deletealerts/<?= $lista_tipo;?>">
