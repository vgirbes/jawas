<?php

/**
 * @group Model
 */

class UsuariosTest extends CIUnit_TestCase
{
	private $_usuarios;
	
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
	}
	
	public function setUp()
	{
		parent::setUp();
		$this->CI->load->model('usuarios');
		$this->_usuarios = $this->CI->usuarios;
	}

	public function testgetLogin(){
		$result = $this->_usuarios->getLogin('vgirbes', md5('12345'));
		$this->assertArrayHasKey('username', $result[0]);
	}

	public function testgetCountry(){
		$result = $this->CI->usuarios->getCountry(1);
		$this->assertInternalType('string', $result);
	}	

}
