<?php
declare(strict_types=1);

/**
 * DefaultControllerTest.php
 * Project: yaipam
 * User: ktammling
 * Date: 10.06.17
 * Time: 22:38
 */

use PHPUnit\Framework\TestCase;
use Controller\DefaultController;

/*
 * @covers Controller\DefaultController()
 */
final class DefaultControllerTest extends TestCase
{
	public function setUp()
	{
		$_SESSION['Group'] = 3;
	}

	public function testCallIndexAction() {
		$Controller = new DefaultController('DefaultController', 'IndexAction');
		$this->assertTrue($Controller->IndexAction());
	}
}