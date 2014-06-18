	<section id="login-user">
		<div class="login-box">
			<?php echo form_open('users/login');?>
			<ul>
				<li>
					Introduzca su usuario y contraseña
				</li>
				<li>
					<label for="username">Usuario</label>
					<input type="text" name="username" class="input" placeholder="usuario" value=""/>
				</li>
				<li>
					<label for="password">Contraseña</label>
					<input type="password" name="password" class="input" placeholder="contraseña" value=""/>
				</li>
				<li>
					<input type="submit" value="Acceder"/>
				</li>
				<li class="msg">
					<?= (isset($error) ? $error : '');?>
					<?= validation_errors();?>
				</li>
			</ul>
			<?php echo form_close();?>
		</div>
	</section>