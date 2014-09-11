<?php 
$debugger = DEBUG; 
$datos['debugger'] = DEBUG;
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
	<?php if (isset($lista_tipo) && isset($lista_emails)){
	$datos['lista_tipo'] = $lista_tipo;
	$datos['lista_emails'] = $lista_emails;
	$datos['country'] = $country;
	$this->load->view('alertas.php', $datos);	
	}?>
	<?php if (isset($lista_tipo) && $lista_tipo == 'country'){ 
	$datos['query'] = $query;
	$this->load->view('alertas_pais.php', $datos);
	} ?>
	<?php if (isset($errores)){ ?>
	<div id="error">
		<?= $errores;?>
	</div>
	<?php } ?>
	<?php if ($debugger){ ?>
	<div id="status">
		<div id="status-title">Debugger
			<div class="cerrar-status">
				<a href="javascript:cerrar_status()">X</a>
			</div>
			<div class="cerrar-status">
				<a href="javascript:windows('max', '')">O</a>
			</div>
			<div class="cerrar-status">
				<a href="javascript:windows('min', '')">_</a>
			</div>
		</div>
		<div id="status-text">
		Iniciando debugger
		</div>
	</div>
	<?php } ?>
<?php }else{
	$this->load->view('login');
} ?>

<?php $this->load->view('includes/footer.php');?>