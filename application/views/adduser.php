<?php $this->load->view('includes/header.php');?>
<?php if(isset($this->session->userdata['username'])){ ?>
	<section id="main">
<?php $this->load->view('includes/nav.php');?>
	</section>
	<form method="post" class="form-horizontal addusuario" action="<?= base_url();?><?= $this->session->userdata['lang'];?>/users/adduser">
		<fieldset>

		<!-- Form Name -->
		<legend>Crear nuevo usuario</legend>

		<!-- Text input-->
		<div class="control-group">
		  <label class="control-label" for="textinput">Nombre</label>
		  <div class="controls">
		    <input id="textinput" name="name" placeholder="Nombre" class="input-xlarge" required="" type="text">
		    
		  </div>
		</div>

		<!-- Text input-->
		<div class="control-group">
		  <label class="control-label" for="textinput">Email</label>
		  <div class="controls">
		    <input id="textinput" name="email" placeholder="Email" class="input-xlarge" required="" type="text">
		    
		  </div>
		</div>

		<!-- Text input-->
		<div class="control-group">
		  <label class="control-label" for="textinput">Usuario</label>
		  <div class="controls">
		    <input id="textinput" name="usuario" placeholder="Usuario" class="input-xlarge" required="" type="text">
		    
		  </div>
		</div>

		<!-- Password input-->
		<div class="control-group">
		  <label class="control-label" for="passwordinput">Contraseña</label>
		  <div class="controls">
		    <input id="passwordinput" name="password" placeholder="Contraseña" class="input-xlarge" required="" type="password">
		    
		  </div>
		</div>

		<!-- Password input-->
		<div class="control-group">
		  <label class="control-label" for="passwordinput">Repite contraseña</label>
		  <div class="controls">
		    <input id="passwordinput" name="rpassword" placeholder="Contraseña" class="input-xlarge" required="" type="password">
		    
		  </div>
		</div>

		<!-- Select Basic -->
		<div class="control-group">
		  <label class="control-label" for="selectbasic">País</label>
		  <div class="controls">
		    <select id="selectbasic" name="pais" class="input-xlarge">
		      <option value="1">España</option>
		      <option value="2">Francia</option>
		      <option value="4">Bélgica</option>
		    </select>
		  </div>
		</div>

		<!-- Select Basic -->
		<div class="control-group">
		  <label class="control-label" for="selectbasic">Rol</label>
		  <div class="controls">
		    <select id="selectbasic" name="selectrol" class="input-xlarge">
		      <option value="0">Usuario</option>
		      <option value="1">Administrador</option>
		    </select>
		  </div>
		</div>

		<!-- Button -->
		<div class="control-group">
		  <label class="control-label" for="singlebutton"></label>
		  <div class="controls">
		    <button id="singlebutton" name="singlebutton" class="btn">Crear usuario</button>
		  </div>
		</div>
		<?php if (isset($estado)){ ?>
		<div id="error-create">
			<ul>
			<?php
			foreach ($estado as $err){
				echo '<li>'.$err.'</li>';
			}
			?>
			</ul>
		</div>
		<?php } ?>
		</fieldset>
	</form>
	
<?php }else{
	$this->load->view('login');
} ?>

<?php $this->load->view('includes/footer.php');?>