	<section id="login-user">
		<div class="login-box">
			<?php echo form_open('es/users/login');?>
			<ul>
				<li>
					<?= lang('login.text');?>
				</li>
				<li>
					<label for="username"><?= lang('login.usuario');?></label>
					<input type="text" name="username" class="input" placeholder="usuario" value=""/>
				</li>
				<li>
					<label for="password"><?= lang('login.password');?></label>
					<input type="password" name="password" class="input" placeholder="contraseña" value=""/>
				</li>
				<li>
					<input type="submit" class="btn" value="Acceder"/>
				</li>
				<li class="msg">
					<?= (isset($error) ? $error : '');?>
					<?= validation_errors();?>
				</li>
			</ul>
			<?php echo form_close();?>
		</div>
	</section>