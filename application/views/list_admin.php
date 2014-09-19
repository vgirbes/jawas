	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <div class="container-fluid" style="margin-top:80px;">
    	<div class="jumbotron">
    		<div class="container-fluid">
	    		<div class="col-md-4">
	    			<h2><span class="glyphicon glyphicon-briefcase"></span> <?= lang('menu.proveedores');?></h2>
	    			<?php foreach ($list_providers as $row){
	    				echo '<a href="'.$row['url'].'">'.$row['name'].'</a><br/>';
	    			} ?>
	    			</div>
	    		<div class="col-md-4">
	    			<h2><span class="glyphicon glyphicon-user"></span> <?= lang('admin.usuarios');?></h2>
	    			<?php foreach ($list_users as $row){
	    				echo '<a href="'.$row['url'].'">'.$row['name'].'</a><br/>';
	    			} ?>
	    		</div>
	    		<div class="col-md-4">
	    			<h2><span class="glyphicon glyphicon-cog"></span> <?= lang('admin.config');?></h2>
	    			<?php foreach ($list_config as $row){
	    				echo '<a href="'.$row['url'].'">'.$row['name'].'</a><br/>';
	    			} ?>
	    		</div>
			</div>
		</div>
    </div>
	<div id="msg-import">
		<?= lang('list_admin.text');?><br/><br/>
		<div id="alert-m" style="display:none;">
			<a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/alerts/stocks" class="boton"><?= lang('list_admin.lista_stock');?></a><br/><br/>
			<a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration/alerts/prices" class="boton"><?= lang('list_admin.lista_precios');?></a><br/><br/>
			<hr></hr>
			<a href="javascript:show_alert()" class="boton"><?= lang('list_admin.volver');?></a><br/><br/>
		</div>
	</div>