	<div id="msg-import">
		<div class="info-title"><?= lang('global.informacion');?></div><br/>
		<?php if (count($lista_ficheros)<= 0){?>
			No hay ningún fichero generado.<br/><br/>
		<?php } ?>
		<?php foreach ($lista_ficheros as $row){
			echo lang('list_msg.nombre_fichero').':<br/> '.$row['file_name'].'<br/>';
			echo lang('list_msg.fecha').': '.$row['date'].'<br/>';
			echo '<br/><a class="boton" href="'.$row['file'].'">'.lang('list_msg.descargar').'</a>';
			echo '<hr></hr>';
		} ?>
	</div>