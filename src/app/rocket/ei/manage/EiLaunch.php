<?php

namespace rocket\ei\manage;

use n2n\core\container\N2nContext;
use rocket\ei\EiEngine;
use rocket\ei\manage\frame\EiFrame;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\security\EiPermissionManager;
use rocket\ei\manage\gui\CachedEiGuiModelFactory;
use rocket\ei\CachedEiDefFactory;
use rocket\ei\manage\gui\EiGuiModelFactory;
use n2n\persistence\orm\EntityManager;

class EiLaunch {
	/**
	 * @var EiFrame[] $eiFrames
	 */
	private array $eiFrames = [];

	private CachedEiGuiModelFactory $cachedEiGuiModelFactory;

	function __construct(private N2nContext $n2nContext, private EiPermissionManager $eiPermissionManager,
			private EntityManager $entityManager) {
		$this->cachedEiGuiModelFactory = new CachedEiGuiModelFactory(new EiGuiModelFactory($this->n2nContext, $this));
	}

	function getN2nContext(): N2nContext {
		return $this->n2nContext;
	}

	function getEiPermissionManager(): EiPermissionManager {
		return $this->eiPermissionManager;
	}

	function getEntityManager(): EntityManager {
		return $this->entityManager;
	}

	function createRootEiFrame(EiEngine $eiEngine): EiFrame {
		IllegalStateException::assertTrue(empty($this->eiFrames));
		return $this->eiFrames[] = $eiEngine->createRootEiFrame($this);
	}

	function getEiGuiModelCache(): CachedEiGuiModelFactory {
		return $this->cachedEiGuiModelFactory;
	}


}