<?php

namespace rocket\impl\ei\component\prop\string;

use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\enum\bo\EnumTestObj;
use PHPUnit\Framework\TestCase;
use testmdl\enum\bo\InvalidEnumTestObj;
use n2n\util\ex\err\ConfigurationError;
use rocket\impl\ei\component\prop\enum\EnumEiPropNature;
use testmdl\string\bo\CkeTestObj;
use rocket\impl\ei\component\prop\string\cke\CkeEiPropNature;
use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use testmdl\string\bo\CkeCssConfigMock;
use testmdl\string\bo\CkeLinkProviderMock;

class CkeEiPropNatureSetupTest extends TestCase {

	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	function testAnnotatedCke(): void {
		$spec = SpecTestEnv::setUpSpec([CkeTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(CkeTestObj::class);
		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(1, $eiProps);

		$ckeEiPropNature = $eiProps['ckeStr1']->getNature();
		assert($ckeEiPropNature instanceof CkeEiPropNature);
		$ckeConfig = $ckeEiPropNature->getCkeConfig();

		$this->assertInstanceOf(CkeCssConfigMock::class, $ckeConfig->getCssConfig());
		$this->assertCount(1, $ckeConfig->getLinkProviders());
		$this->assertInstanceOf(CkeLinkProviderMock::class, $ckeConfig->getLinkProviders()[0]);
	}
}