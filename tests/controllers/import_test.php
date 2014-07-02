<?php
class ImportTest extends CIUnit_TestCase
{
	public function setUp()
	{
		// Set the tested controller
		$this->CI = set_controller('import');
	}
	
	public function testcomdep()
	{
		// Call the controllers method
		$this->CI->comdep();
		
		// Fetch the buffered output
		$out = output();
		
		// Check if the content is OK
		$this->assertSame(0, preg_match('/(error|notice)/i', $out));
	}

	public function testview_comdep()
	{
		// Call the controllers method
		$this->CI->view();         
        $out = output();
		
		// Check if the content is OK
		$this->assertSame(0, preg_match('/(error|notice)/i', $out));
	}

	public function testatyse()
	{
		// Call the controllers method
		$this->CI->atyse();         
        $out = output();
		
		// Check if the content is OK
		$this->assertSame(0, preg_match('/(error|notice)/i', $out));
	}

	public function testindex(){
		// Call the controllers method
		$this->CI->index();
		
		// Fetch the buffered output
		$out = output();
		
		// Check if the content is OK
		$this->assertSame(0, preg_match('/(error|notice)/i', $out));
	}

}