<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Norauto</title>
		<link rel="stylesheet" type="text/css" href="<?php echo asset_url();?>css/style.css">
		<?php 
		if (isset($css_files)){
			foreach($css_files as $file): ?>
				<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
			<?php endforeach; ?>
			<?php foreach($js_files as $file): ?>
				<script src="<?php echo $file; ?>"></script>
			<?php endforeach; ?>
		<?php } ?>
	</head>
	<body>
