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
		<?= lang('import_msg.no_informacion');?>
		<?php } ?>
		<hr></hr>
		<div class="info-title"><?= lang('import_msg.importacion_diaria');?></div><br/>
		<?php if (isset($process_all) && $error_process == false){
				echo lang('import_msg.el_proceso').' '.strtoupper($process->flag).' '.lang('import_msg.sigue_activo').'<br/>';
				echo lang('import_msg.inicio_accion').' '.$process->f_start.'<br/>';
				echo lang('import_msg.tiempo_estimado').' '.$time_process;
		}?>
		<?php if (isset($process_all) && $error_process != false){
			echo $error_process->msg;
		}?>
		<?php if (!isset($process_all)){
			echo lang('import_msg.proceso_ok');
		}?>
		<br/><br/>
		<?php if (isset($process)){ ?>
			<div class="info-title"><?= lang('import_msg.importacion_usuario');?></div><br/>
			<?php if (!isset($process_all) && $error_process == false){
				echo lang('import_msg.el_proceso').' '.strtoupper($process->flag).' '.lang('import_msg.sigue_activo').'<br/>';
				echo lang('import_msg.inicio_accion').' '.$process->f_start.'<br/>';
				echo lang('import_msg.tiempo_estimado').' '.$time_process;
			}else{
				echo lang('import_msg.ninguna_accion');
			}?>
			<?php if (!isset($process_all) && $error_process != false){
				echo $error_process->msg;
			}?>
		<?php } ?>
		<br/><br/>
		<?php if ($editable){ ?>
		<a class="boton" href="<?= base_url();?><?= $this->session->userdata['lang'];?>/import/view"><?= lang('import_msg.ver_datos');?></a>
		<?php } ?>
	</div>