<?php $this->load->view('includes/header.php');?>
<?php if(isset($this->session->userdata['username'])){ ?>
	<section id="main">
<?php $this->load->view('includes/nav.php');?>
	</section>
<?php }else{
	$this->load->view('login');
} ?>

<?php $this->load->view('includes/footer.php');?>