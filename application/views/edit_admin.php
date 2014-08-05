<?php $this->load->view('includes/header.php');?>
<?php if(isset($this->session->userdata['username'])){ ?>
<?php $this->load->view('includes/nav.php');?>
		<?php echo $output; ?>
<?php }else{
	$this->load->view('login');
} ?>
<?php $this->load->view('includes/footer.php');?>