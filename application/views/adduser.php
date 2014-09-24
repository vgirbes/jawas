<?php $this->load->view('includes/header.php');?>
<?php if(isset($this->session->userdata['username'])){ ?>
	<section id="main">
<?php $this->load->view('includes/nav.php');?>
	</section>
	<form method="post" class="form-horizontal addusuario" action="<?= base_url();?><?= $this->session->userdata['lang'];?>/users/adduser">
		<fieldset>

		<legend><?= lang('adduser.form_name');?></legend>

		<div class="control-group">
		  <label class="control-label" for="textinput"><?= lang('adduser.name');?></label>
		  <div class="controls">
		    <input id="textinput" name="name" placeholder="<?= lang('adduser.name');?>" class="input-xlarge" required="" type="text">
		    
		  </div>
		</div>

		<div class="control-group">
		  <label class="control-label" for="textinput">Email</label>
		  <div class="controls">
		    <input id="textinput" name="email" placeholder="Email" class="input-xlarge" required="" type="text">
		    
		  </div>
		</div>

		<div class="control-group">
		  <label class="control-label" for="textinput"><?= lang('adduser.user');?></label>
		  <div class="controls">
		    <input id="textinput" name="usuario" placeholder="<?= lang('adduser.user');?>" class="input-xlarge" required="" type="text">
		    
		  </div>
		</div>

		<div class="control-group">
		  <label class="control-label" for="passwordinput"><?= lang('adduser.password');?></label>
		  <div class="controls">
		    <input id="passwordinput" name="password" placeholder="<?= lang('adduser.password');?>" class="input-xlarge" required="" type="password">
		    
		  </div>
		</div>

		<div class="control-group">
		  <label class="control-label" for="passwordinput"><?= lang('adduser.rpassword');?></label>
		  <div class="controls">
		    <input id="passwordinput" name="rpassword" placeholder="<?= lang('adduser.rpassword');?>" class="input-xlarge" required="" type="password">
		    
		  </div>
		</div>

		<div class="control-group">
		  <label class="control-label" for="selectbasic"><?= lang('adduser.country');?></label>
		  <div class="controls">
		    <select id="selectbasic" name="country" class="input-xlarge">
		    	<?php
		    	foreach ($countries->result() as $country){
		    		echo '<option value="'.$country->id.'">'.$country->name.'</option>'."\n";
		    	}
		    	?>
		    </select>
		  </div>
		</div>

		<div class="control-group">
		  <label class="control-label" for="selectbasic">Rol</label>
		  <div class="controls">
		    <select id="selectbasic" name="rol" class="input-xlarge">
		      <option value="0"><?= lang('adduser.rol_user');?></option>
		      <option value="1"><?= lang('adduser.rol_admin');?></option>
		    </select>
		  </div>
		</div>

		<div class="control-group">
		  <label class="control-label" for="selectbasic"><?= lang('other_provider.activo');?></label>
		  <div class="controls">
		  	<input type="checkbox" name="activo" class="input-xlarge" value="1">
		  </div>
		</div>

		<div class="control-group">
		  <label class="control-label" for="singlebutton"></label>
		  <div class="controls">
		    <button id="singlebutton" name="singlebutton" class="btn"><?= lang('adduser.button');?></button>&nbsp;&nbsp;&nbsp;
		    <a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration" class="btn"><?= lang('adduser.cancel');?></a> 
		  </div>

		</div>
		<?php if (isset($estado)){ ?>
		<div id="error-create">
			<ul>
			<?php
			foreach ($estado as $err){
				echo '<li>'.$err.'</li>'."\n";
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