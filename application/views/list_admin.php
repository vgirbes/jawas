	<div id="msg-import">
		<div class="info-title"><?= lang('global.informacion');?></div><br/>
		Desde aquí podrá administrar los datos de la aplicación.<br/><br/>
		<?php foreach ($list_admin as $row){
			echo '<a href="'.$row['url'].'" class="boton">'.$row['name'].'</a><br/><br/>';
		} ?>
	</div>