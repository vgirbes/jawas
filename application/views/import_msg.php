	<div id="msg-import">
		<div class="info-title">Información</div>
		<br/>Existe una importación previa en la base de datos.<br/>
		A continuación se muestra un resumen:<br/><br/>
		<?php foreach ($import_state as $row){
			echo 'Fecha de importación:<br/> '.$row['fecha'].'<br/><br/>';
			echo 'Estado:<br/> Importación de '.$row['flag'].'<br/><br/>';
			echo 'Archivo importado:<br/> '.$row['filename'];
		} ?>
		<br/><br/>
		<a class="boton" href="<?= base_url();?>import/view">Ver datos</a>
	</div>