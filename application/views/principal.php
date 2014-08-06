<?php 
$debugger = true; 
$datos['debugger'] = true;
?>
<?php $this->load->view('includes/header.php', $datos);?>
<?php if(isset($this->session->userdata['username'])){ ?>
	<section id="main">
<?php $this->load->view('includes/nav.php');?>
	</section>
	<?php if (isset($import_state)){ 
	$this->load->view('import_msg.php', $import_state);	
	} ?>
	<?php if (isset($lista_ficheros)){ 
	$this->load->view('list_msg.php', $lista_ficheros);	
	} ?>
	<?php if (isset($admin)){ 
	$this->load->view('list_admin.php', $list_admin);
	} ?>
	<?php if (isset($errores)){ ?>
	<div id="error">
		<?= $errores;?>
	</div>
	<?php } ?>
	<?php if ($debugger){ ?>
	<div id="status">
		<div id="status-title">Debugger<div id="cerrar-status" style=""><a href="javascript:cerrar_status()">X</a></div></div>
		<div id="status-text">
		Iniciando debugger
		</div>
	</div>
	<?php } ?>
<?php }else{
	$this->load->view('login');
} ?>

<?php $this->load->view('includes/footer.php');?>