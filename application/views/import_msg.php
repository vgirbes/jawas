	<div id="msg-import">
		<div class="info-title"><?= lang('global.informacion');?></div>
		<br/><?= lang('import_msg.text');?>:<br/><br/>
		<?php foreach ($import_state as $row){
			echo lang('import_msg.f_importacion').':<br/> '.$row['fecha'].'<br/><br/>';
			echo lang('import_msg.estado').':<br/> '.lang('import_msg.importacion').' '.$row['flag'].'<br/><br/>';
			echo lang('import_msg.archivo_importado').':<br/> '.($row['filename'] == '' ? lang('import_msg.no_informacion') : $row['filename']);
		} ?>
		<br/><br/>
		<a class="boton" href="<?= base_url();?>import/view"><?= lang('import_msg.ver_datos');?></a>
	</div>