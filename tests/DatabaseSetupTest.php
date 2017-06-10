<?php
declare(strict_types=1);
/**
 * DatabaseSetupTest.php
 * Project: yaipam
 * User: ktammling
 * Date: 10.06.17
 * Time: 23:42
 */

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\Tools\SchemaTool;

final class DatabaseSetupTest extends TestCase
{
	public function testDatabaseSetup() {
		global $EntityManager;

		$tool = new SchemaTool($EntityManager);
		$classes = array(
			$EntityManager->getClassMetadata('Entity\Addresses'),
			$EntityManager->getClassMetadata('Entity\Otv'),
			$EntityManager->getClassMetadata('Entity\OtvDomains'),
			$EntityManager->getClassMetadata('Entity\Prefixes'),
			$EntityManager->getClassMetadata('Entity\PrefixesVrfs'),
			$EntityManager->getClassMetadata('Entity\User'),
			$EntityManager->getClassMetadata('Entity\VlanDomains'),
			$EntityManager->getClassMetadata('Entity\Vlans'),
			$EntityManager->getClassMetadata('Entity\Vrfs')
		);
		$tool->createSchema($classes);
	}
}