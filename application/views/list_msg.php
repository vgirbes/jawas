	<div id="msg-import">
		<div class="info-title">Información</div><br/>
		<?php foreach ($lista_ficheros as $row){
			echo 'Nombre del fichero:<br/> '.$row['file_name'].'<br/>';
			echo 'Fecha: '.$row['date'].'<br/>';
			echo '<br/><a class="boton" href="'.$row['file'].'">Descargar</a>';
			echo '<hr></hr>';
		} ?>
	</div>