		<nav id="menu">
			<ul id="menu-ul">
				<li><a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/import"><?= lang('menu.importar_datos');?></a></li>
				<li><a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/providers/view"><?= lang('menu.proveedores');?></a></li>
				<li><a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/files"><?= lang('menu.ficheros');?></a></li>
				<?php if ($this->session->userdata['rol'] == 1){ ?>
				<li class="admin-link"><a class="admin-anchor" href="<?= base_url();?><?= $this->session->userdata['lang'];?>/administration">Administración</a></li>
				<?php } ?>
				<li><a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/users/logout" title="<?= lang('menu.logout');?>"><?= lang('menu.logout');?></a></li>
			</ul>
		</nav>
		<?php if ($this->uri->segment(2)=='import'){ ?>
		<div id="submenu">
			<ul id="menu-ul">
				<li><a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/import/comdep"><?= lang('submenu.comdep');?></a></li>
				<li><a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/import/atyse"><?= lang('submenu.atyse');?></a></li>
				<li><a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/import/mch"><?= lang('submenu.mch');?></a></li>
				<li><a href="<?= base_url();?><?= $this->session->userdata['lang'];?>/import/generate"><?= lang('submenu.ficheros');?></a></li>
			</ul>
		</div>
		<?php } ?>
	