<?php
declare(strict_types=1);

/**
 * VrfsControllerTest.php
 * Project: yaipam
 * User: ktammling
 * Date: 10.06.17
 * Time: 23:11
 */

use PHPUnit\Framework\TestCase;
use Controller\VrfsController;

/*
 * @covers Controller\VrfsController()
 */
final class VrfsControllerTest extends TestCase
{
	public function setUp()
	{
		$_SESSION['Group'] = 3;
	}

	public function testCallIndexAction() {
		$Controller = new VrfsController('VrfsController', 'IndexAction');
		$this->assertTrue($Controller->IndexAction());
	}

	public function testCallEditAction() {
		$Controller = new VrfsController('VrfsController', 'EditAction');
		$this->assertTrue($Controller->EditAction());
	}

	public function testCallAddAction() {
		$Controller = new VrfsController('VrfsController', 'AddAction');
		$this->assertTrue($Controller->AddAction());
	}

	public function testCallDeleteAction() {
		$Controller = new VrfsController('VrfsController', 'DeleteAction');
		$this->assertTrue($Controller->DeleteAction());
	}
}