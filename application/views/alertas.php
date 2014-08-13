	<div id="status-save"></div>
	<div id="izq-mails-back"></div>
	<div id="izq-mails">
		<div class="import-mails">
			<span class="title-mails">
				<?= lang('alerts.lista_titulo');?> <?= $lista_tipo;?><br/><br/>
				<a href="#" class="boton btn-alert"><?= lang('alerts.importar_contacto');?></a><br/><br/>
				<form method="post" id="f_alertas" action="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/alerts/<?= $lista_tipo;?>">
					<input class="input_type" required="" type="text" placeholder="Email" name="email" id="email" value="">
					<br/>
					<a class="boton btn-alert" href="javascript:save_mail();"><?= lang('alerts.add_contact');?></a><br/><br/>
					<input type="hidden" name="url" id="url" value="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/alerts/<?= $lista_tipo;?>">
					<input type="hidden" name="type" id="type" value="<?= $lista_tipo;?>">
				</form>
			</span>
		</div>
	</div>
	<div id="der-mails-back"></div>
	<div id="der-mails">
		<?php 
		if (isset($lista_emails) && ($lista_emails)){
			foreach ($lista_emails->result() as $row){
				echo '<div class="mail-contact" title="'.$row->email.'" id="email_'.$row->id.'">'."\n";
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
