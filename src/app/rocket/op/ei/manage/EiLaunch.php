<?php

namespace rocket\op\ei\manage;

use n2n\core\container\N2nContext;
use rocket\op\ei\EiEngine;
use rocket\op\ei\manage\frame\EiFrame;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\manage\security\EiPermissionManager;
use rocket\op\ei\manage\gui\LazyEiGuiModelStore;
use rocket\op\ei\CachedEiDefFactory;
use rocket\op\ei\manage\gui\EiGuiModelFactory;
use n2n\persistence\orm\EntityManager;

class EiLaunch {
	/**
	 * @var EiFrame[] $eiFrames
	 */
	private array $eiFrames = [];

	function __construct(private N2nContext $n2nContext, private EiPermissionManager $eiPermissionManager,
			private EntityManager $entityManager) {
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
}