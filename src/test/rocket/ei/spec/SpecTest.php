<?php
namespace rocket\ei\util\spec;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\BasicTestObj;
use testmdl\bo\Basic2TestObj;
use testmdl\bo\Basic3TestObj;
use rocket\test\GeneralTestEnv;

class SpecTest extends TestCase {

	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	function testInit() {
		$spec = SpecTestEnv::setUpSpec([BasicTestObj::class, Basic2TestObj::class, Basic3TestObj::class]);

		$eiTypes = $spec->getEiTypes();
		$this->assertCount(3, $eiTypes);

		$menuGroups = $spec->getMenuGroups();
		$this->assertCount(2, $menuGroups);

		$this->assertEquals('Super Duper Guper', $menuGroups['super-duper']->getLabel());
		$launchPads = $menuGroups['super-duper']->getLaunchPads();
		$this->assertCount(2, $launchPads);

		$this->assertEquals('Not Super Duper Gruper', $menuGroups['not-super-duper']->getLabel());
		$launchPads = $menuGroups['not-super-duper']->getLaunchPads();
		$this->assertCount(1, $launchPads);

	}
}