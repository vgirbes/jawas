	<div id="status-notif">
		<div id="status-title-notif"><?= lang('general.mensajes');?>
			<div class="cerrar-status-notif">
				<a href="javascript:close_messages()">X</a>
			</div>
			<div class="cerrar-status-notif">
				<a href="javascript:windows('max', '-notif')">O</a>
			</div>
			<div class="cerrar-status-notif">
				<a href="javascript:windows('min', '-notif')">_</a>
			</div>
		</div>
		<div id="status-text-notif">
		
		</div>
	</div>
	<footer>
		<?= lang('footer');?> <span class="norauto">Norauto</span>
		<div id="mch-status">
			MCH
			<div class="mch-led"></div>
		</div>
	</footer>
</body>
</html>