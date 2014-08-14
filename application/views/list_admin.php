	<div id="msg-import">
		<div class="info-title"><?= lang('global.informacion');?></div><br/>
		<?= lang('list_admin.text');?><br/><br/>
		<div id="lista-m" style="display:block;">
		<?php foreach ($list_admin as $row){
			echo '<a href="'.$row['url'].'" class="boton">'.$row['name'].'</a><br/><br/>';
		} ?>
		</div>
		<div id="alert-m" style="display:none;">
			<a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/alerts/stocks" class="boton"><?= lang('list_admin.lista_stock');?></a><br/><br/>
			<a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/alerts/prices" class="boton"><?= lang('list_admin.lista_precios');?></a><br/><br/>
			<hr></hr>
			<a href="javascript:show_alert()" class="boton"><?= lang('list_admin.volver');?></a><br/><br/>
		</div>
	</div>