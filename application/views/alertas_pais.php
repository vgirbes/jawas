	<div id="msg-import">
		<div class="info-title"><?= lang('global.informacion');?></div><br/>
		<br/>
		<div id="lista-m" style="display:block;">
		<?php foreach ($query->result() as $row){
			echo '<a href="'.$_SERVER['REQUEST_URI'].'/'.$row->id.'" class="boton">'.$row->name.'</a><br/><br/>';
		} ?>
		</div>
	</div>