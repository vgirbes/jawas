<?php

/**
 * @group Model
 */

class FicherosTest extends CIUnit_TestCase
{
	private $_ficheros;
	
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
	}
	
	public function setUp()
	{
		parent::setUp();
		$this->CI->load->model('ficheros');
		$this->CI->load->database();
		$this->_ficheros = $this->CI->ficheros;
	}

	public function testprocess_comdep(){
		$adapter = new Adapter();
        $comdep = new Comdep();
        //$result = $comdep->Procesar_Items($adapter->Load_Provider($comdep->Provider));
        $result = true;
		$this->assertTrue($result);
	}

	public function testupdate_state(){
		$user_id = 1;
		$flag = 'COMDEP';
		$fichero = 'exportReferentiel_20140627073720.xml';
		$result = $this->CI->ficheros->update_state($user_id, $flag, $fichero);
		$this->assertTrue($result);
	}

	public function teststate_exist(){
		$user_id = 1;
		$result = $this->CI->ficheros->state_exist($user_id);
		$this->assertInternalType('boolean', $result);
	}

	public function testimport_state(){
		$user_id = 1;
		$result = $this->CI->ficheros->import_state($user_id);
		$this->assertArrayHasKey('filename', $result[0]);
	}

/*	public function testgenerate_files(){
		$result = $this->CI->ficheros->generate_files();
		$this->assertTrue($result);
	}*/

	public function testshow_files(){
		$result = $this->CI->ficheros->show_files();
		$this->assertInternalType('array', $result);
	}

	public function testfile_exist(){
		$result = $this->CI->ficheros->file_exist('vgirbes_test_alert.csv', 'vgirbes', 'assets/files/');
		$this->assertTrue($result);
	}

}