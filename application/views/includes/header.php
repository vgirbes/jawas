<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Norauto</title>
		<link rel="stylesheet" type="text/css" href="<?php echo asset_url();?>css/style.css">
		<link type="text/css" rel="stylesheet" href="<?php echo asset_url();?>grocery_crud/themes/twitter-bootstrap/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo asset_url();?>css/elem.css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
  		<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
  		<script>
  			$(function() {
    			$( "#msg-import" ).draggable();
    			$( ".login-box" ).draggable();
  			});
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
