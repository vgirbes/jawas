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
  		<script>
  			$(function() {
    			$( "#msg-import" ).draggable();
    			$( ".login-box" ).draggable();
  			});
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
		<style>
			body{
				background-image: url(<?php echo asset_url();?>img/logo_norauto.gif);
				background-repeat: no-repeat;
				background-attachment: fixed;
				background-position: center center;
			}
		</style>
	</head>
	<body>
