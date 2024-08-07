<?php

namespace rocket\test;

use n2n\persistence\orm\model\EntityModelFactory;
use n2n\impl\persistence\orm\property\CommonEntityPropertyProvider;
use rocket\op\spec\setup\SpecConfigLoader;
use n2n\persistence\orm\model\EntityModelManager;
use rocket\op\spec\Spec;
use n2n\util\magic\impl\SimpleMagicContext;
use rocket\impl\ei\component\provider\RocketEiComponentNatureProvider;
use n2n\core\container\N2nContext;
use n2n\core\util\N2nUtil;
use ReflectionClass;
use n2n\core\container\TransactionManager;
use n2n\context\LookupManager;
use n2n\core\VarStore;
use n2n\web\http\HttpContext;
use n2n\l10n\N2nLocale;
use ReflectionParameter;
use n2n\core\module\ModuleManager;
use n2n\util\ex\UnsupportedOperationException;
use testmdl\bo\ModTestObj;
use testmdl\bo\ModTestMod;
use rocket\core\model\Rocket;
use rocket\op\spec\SpecFactory;
use n2n\test\TestEnv;
use n2n\core\cache\AppCache;
use n2n\core\ext\N2nMonitor;
use n2n\core\ext\N2nHttp;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use testmdl\relation\bo\IntegratedSrcTestObj;
use n2n\util\uri\Url;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\mask\EiMask;

class SpecTestEnv {

	static function setUpSpec(array $entityClassNames): Spec {
		$emm = new EntityModelManager($entityClassNames, new EntityModelFactory([CommonEntityPropertyProvider::class]));

		$natureProvider = new RocketEiComponentNatureProvider();

		$n2nContext = TestEnv::getN2nContext();
		$n2nContext->putLookupInjection(RocketEiComponentNatureProvider::class, $natureProvider);

		$class = new ReflectionClass($natureProvider);
		$property = $class->getProperty('magicContext');
		$property->setAccessible(true);
		$property->setValue($natureProvider, $n2nContext);

		$scl = new SpecConfigLoader(new TestModuleConfigSource(['rocket', 'testmdl']), ['rocket', 'testmdl'],
				$n2nContext);

		$spec = (new SpecFactory($scl, $emm))->create();

		$n2nContext->lookup(Rocket::class)->setSpec($spec);


		return $spec;
	}

	static function setUpEiFrame(Spec $spec, EiMask $eiMask): EiFrame {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->setBaseUrl(Url::create('/admin'));
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		return $eiFrame;
	}
}

class N2nContextMock extends SimpleMagicContext implements N2nContext {

	function util(): N2nUtil {
		throw new UnsupportedOperationException();
	}

	public function getTransactionManager(): TransactionManager {
		throw new UnsupportedOperationException();
	}

	public function getModuleManager(): ModuleManager {
		throw new UnsupportedOperationException();
	}

	public function getModuleConfig(string $namespace) {
		throw new UnsupportedOperationException();
	}

	public function getVarStore(): VarStore {
		throw new UnsupportedOperationException();
	}

	public function isHttpContextAvailable(): bool {
		throw new UnsupportedOperationException();
	}

	public function getHttpContext(): HttpContext {
		throw new UnsupportedOperationException();
	}

	public function getAppCache(): AppCache {
		throw new UnsupportedOperationException();
	}

	public function getN2nLocale(): N2nLocale {
		throw new UnsupportedOperationException();
	}

	public function setN2nLocale(N2nLocale $n2nLocale) {
		throw new UnsupportedOperationException();
	}

	public function getLookupManager(): LookupManager {
		throw new UnsupportedOperationException();
	}

	function putLookupInjection(string $id, object $obj): void {
		throw new UnsupportedOperationException();
	}

	function removeLookupInjection(string $id): void {
		throw new UnsupportedOperationException();
	}

	function clearLookupInjections(): void {
		throw new UnsupportedOperationException();
	}

	function finalize(): void {
		throw new UnsupportedOperationException();
	}

	function isFinalized(): bool {
		throw new UnsupportedOperationException();
	}

	function getHttp(): ?N2nHttp {
		throw new UnsupportedOperationException();
	}

	function getMonitor(): ?N2nMonitor {
		throw new UnsupportedOperationException();
	}
}