	<div id="msg-import">
		<div class="info-title"><?= lang('global.informacion');?></div>
		<?php if (count($import_state)> 0){?>
		<br/><?= lang('import_msg.text');?>:<br/><br/>
		<?php foreach ($import_state as $row){
			echo lang('import_msg.f_importacion').':<br/> '.$row['fecha'].'<br/><br/>';
			echo lang('import_msg.estado').':<br/> '.lang('import_msg.importacion').' '.$row['flag'].'<br/><br/>';
			echo lang('import_msg.archivo_importado').':<br/> '.($row['filename'] == '' ? lang('import_msg.no_informacion') : $row['filename']);
		} ?>
		<br/>
		<?php } else{ ?>
		<br/>
		No hay información disponible.
		<?php } ?>
		<hr></hr>
		<div class="info-title">Importación de datos diaria</div><br/>
		<?php if (isset($process_global) && $error_process == false){
			echo 'El proceso sigue activo. Tiempo estimado: '.$time_process;
		}?>
		<?php if (isset($process_global) && $error_process != false){
			echo $error_process->msg;
		}?>
		<?php if (!isset($process_global)){
			echo 'Proceso finalizado con éxito.';
		}?>
		<br/><br/>
		<?php if (isset($process)){ ?>
			<div class="info-title">Importación iniciada por el usuario</div><br/>
			<?php if (!isset($process_global) && $error_process == false){
				echo 'El proceso '.strtoupper($process->flag).' sigue activo.<br/>';
				echo 'Inicio de la acción: '.$process->f_start.'<br/>';
				echo 'Tiempo estimado: '.$time_process;
			}?>
			<?php if (!isset($process_global) && $error_process != false){
				echo $error_process->msg;
			}?>
		<?php } ?>
		<br/><br/>
		<?php if ($editable){ ?>
		<a class="boton" href="<?= base_url();?><?= $this->session->userdata['lang'];?>/import/view"><?= lang('import_msg.ver_datos');?></a>
		<?php } ?>
	</div>