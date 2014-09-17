<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Norauto</title>
		<link rel="stylesheet" type="text/css" href="<?php echo asset_url();?>css/style.css">
		<link type="text/css" rel="stylesheet" href="<?php echo asset_url();?>grocery_crud/themes/twitter-bootstrap/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo asset_url();?>css/elem.css">
		
		<link href="<?php echo asset_url();?>img/favicon.ico" rel="shortcut icon" type="image/x-icon">

		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
  		<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
		<script src="<?= asset_url();?>js/debugger.js"></script>
		<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/base/jquery-ui.css"/>
		<?php if (isset($lista_tipo)){?>
		<script src="<?= asset_url();?>js/alerts.js"></script>
		<?php } ?>
		<script src="<?= asset_url();?>js/utils.js"></script>
  		<script>
  			<?php if (isset($this->session->userdata["username"])){ ?>
  			function send_request(type)
			{
				$.ajax({
					 type: 'POST',
					 url: '<?php echo base_url(); ?><?= $this->session->userdata["lang"];?>/import/'+type, 
					 success: function(resp) { 
					 	
					 }
				});
				
                window.setTimeout(reloadpage, 1000)

			 }

			 function reloadpage(){
			 	window.location = '<?= base_url();?><?= $this->session->userdata["lang"];?>/import';
			 }
			 
			 window.setInterval(function() {
			 		$.ajax({
					 type: 'POST',
					 url: '<?php echo base_url(); ?><?= $this->session->userdata["lang"];?>/status', 
					 success: function(resp) { 
					 	respuesta = eval('(' + resp + ')');
					 	if (respuesta.stat == ''){
					 		$('.mch-led').css({'background-color':'red'});
					 	}else{
					 		$('.mch-led').css({'background-color':'green'});
					 		$('.mch-led').attr('title', respuesta.stat);
					 	}
					 }
					})
				}, 10000);

			 window.setInterval(function() {
			 		$.ajax({
					 type: 'POST',
					 url: '<?php echo base_url(); ?><?= $this->session->userdata["lang"];?>/status/get_notify', 
					 success: function(resp) { 
					 	respuesta = eval('(' + resp + ')');
					 	if (respuesta.msg != ''){
					 		$('#notif-num').html('<a href="javascript:show_notif()">'+respuesta.num+'</a>').effect('bounce', 1000);
					 	}
					 	$('#status-text-notif').html(respuesta.msg);

					 }
					})
				}, 2000);

			 function close_messages(){
			 	url = '<?php echo base_url(); ?><?= $this->session->userdata["lang"];?>/status/close_notify';
			 	close_notify(url);
			 }
			 
			 <?php if (isset($debugger) && $debugger){ ?>
			 window.setInterval(function() {
			 	$.ajax({
					 type: 'POST',
					 url: '<?php echo base_url(); ?><?= $this->session->userdata["lang"];?>/debug', 
					 success: function(resp) { 
					 	respuesta = eval('(' + resp + ')');
				 		$('#status-text').prepend(respuesta.msg);
				 		$('#status')
						    .resizable({
						        start: function(e, ui) {
						        	$('#status-text').css({ 'max-height':'100%' });
						        	$('#status-text').css({ 'height':'auto' });
						        	$('#status-text').css({ 'max-height':$('#status-text').height()+'px' });
						        },
						        resize: function(e, ui) {

						        },
						        stop: function(e, ui) {
						        	$('#status-text').css({ 'max-height':'90%' });
						        	$('#status-text').css({ 'height':'auto' });
						        	$('#status-text').css({ 'max-height':$('#status-text').height()+'px' });
						        }
						    });
					 }

					
					})
			 }, 1000);

			 <?php } ?>
			 <?php } ?>
  		</script>
		<?php 
		if (isset($css_files)){
			foreach($css_files as $file): ?>
				<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
			<?php endforeach; ?>
			<?php foreach($js_files as $file): ?>
				<script src="<?php echo $file; ?>"></script>
			<?php endforeach; ?>
		<?php } ?>
		<?php if ($this->uri->segment(1)=='import'){ ?>
		<style>
			#options-content{
				margin-top:60px;
			}
		</style>
		<?php } ?>
		<?php if (!isset($list_admin)&&(!isset($other_prov))) {?>
		<style>
			body{
				background-image: url(<?php echo asset_url();?>img/logo_norauto.gif);
				background-repeat: no-repeat;
				background-attachment: fixed;
				background-position: center center;
			}
		</style>
		<?php } ?>
	</head>
	<body>
