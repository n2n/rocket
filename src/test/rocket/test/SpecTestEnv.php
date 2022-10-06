<?php

namespace rocket\test;

use n2n\persistence\orm\model\EntityModelFactory;
use n2n\impl\persistence\orm\property\CommonEntityPropertyProvider;
use rocket\spec\setup\SpecConfigLoader;
use n2n\persistence\orm\model\EntityModelManager;
use rocket\spec\Spec;
use n2n\util\magic\SimpleMagicContext;
use rocket\impl\ei\component\RocketEiComponentNatureProvider;

class SpecTestEnv {

	static function setUpSpec(array $entityClassNames): Spec {
		$emm = new EntityModelManager($entityClassNames, new EntityModelFactory([CommonEntityPropertyProvider::class]));

		$scl = new SpecConfigLoader(new TestModuleConfigSource(['rocket', 'testmdl']),
				['rocket', 'testmdl'], new SimpleMagicContext([
					RocketEiComponentNatureProvider::class => new RocketEiComponentNatureProvider()
				]));

		return new Spec($scl, $emm);
	}
}