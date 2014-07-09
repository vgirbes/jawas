		<nav id="menu">
			<ul id="menu-ul">
				<li><a href="<?= base_url();?>import">Importar datos</a></li>
				<li><a href="<?= base_url();?>providers/view">Proveedores</a></li>
				<li><a href="<?= base_url();?>users/logout" title="Cerrar sesión">Cerrar sesión</a></li>
			</ul>
		</nav>
		<?php if ($this->uri->segment(1)=='import'){ ?>
		<div id="submenu">
			<ul id="menu-ul">
				<li><a href="<?= base_url();?>import/comdep">Importar datos de Comdep</a></li>
				<li><a href="<?= base_url();?>import/atyse">Importar datos de Atyse</a></li>
				<li><a href="<?= base_url();?>import/mch">Importar datos de MCH</a></li>
				<li><a href="<?= base_url();?>import/generate">Generar ficheros</a></li>
			</ul>
		</div>
		<?php } ?>
	