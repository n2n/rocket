<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */

namespace rocket\op\ei\manage;

use n2n\core\container\N2nContext;
use rocket\op\ei\EiEngine;
use rocket\op\ei\manage\frame\EiFrame;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\manage\security\EiPermissionManager;
use rocket\op\ei\manage\gui\LazyEiGuiDeclarationStore;
use rocket\op\ei\CachedEiDefFactory;
use n2n\persistence\orm\EntityManager;
use rocket\op\ei\manage\veto\EiLifecycleMonitor;

class EiLaunch {
	/**
	 * @var EiFrame[] $eiFrames
	 */
	private array $eiFrames = [];

	function __construct(private N2nContext $n2nContext, private EiPermissionManager $eiPermissionManager,
			private EntityManager $entityManager, private EiLifecycleMonitor $eiLifecycleMonitor) {
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

	function getEiLifecycleMonitor(): EiLifecycleMonitor {
		return $this->eiLifecycleMonitor;
	}

	function createRootEiFrame(EiEngine $eiEngine): EiFrame {
		IllegalStateException::assertTrue(empty($this->eiFrames));
		return $this->eiFrames[] = $eiEngine->createRootEiFrame($this);
	}
}