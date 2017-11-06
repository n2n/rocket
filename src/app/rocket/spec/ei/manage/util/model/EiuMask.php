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
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\mask\EiMask;
use n2n\core\container\N2nContext;
use n2n\persistence\orm\EntityManager;
use rocket\spec\ei\manage\EiEntityObj;
use n2n\persistence\orm\store\EntityInfo;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\draft\DraftManager;
use rocket\core\model\Rocket;
use n2n\reflection\CastUtils;

class EiuMask extends EiUtilsAdapter {
	private $eiMask;
	private $n2nContext;
	private $em;
	
	public function __construct(EiMask $eiMask, N2nContext $n2nContext) {
		$this->eiMask = $eiMask;
		$this->n2nContext = $n2nContext;
	}
	
	public function em(): EntityManager {
		if ($this->em === null) {
			$this->em = $this->eiMask->getEiEngine()->getEiType()->lookupEntityManager($this->n2nContext->getPdoPool());
		}
		
		return $this->em;
	}
	
	public function getEiMask(): EiMask {
		return $this->eiMask;
	}
	
	public function getN2nContext(): N2nContext {
		return $this->n2nContext;
	}
	
	public function getDraftManager(): DraftManager {
		$rocket = $this->n2nContext->lookup(Rocket::class);
		CastUtils::assertTrue($rocket instanceof Rocket);
		
		return $rocket->getOrCreateDraftManager($this->em());
	}

	public function getN2nLocale(): N2nLocale {
		return $this->n2nContext->getN2nLocale();
	}
	
	public function containsId($id, int $ignoreConstraints = 0): bool {
		return null !== $this->em()->find($this->getEntityModel()->getClass(), $id);
	}
	/**
	 * @param mixed $id
	 * @throws UnknownEntryException
	 * @return \rocket\spec\ei\manage\EiEntityObj
	 */
	public function lookupEiEntityObj($id, int $ignoreConstraints = 0): EiEntityObj {
		if (null !== ($entity = $this->em()->find($this->getEntityModel()->getClass(), $id))) {
			return new EiEntityObj($id, $entity);
		}
	
		throw new UnknownEntryException('Entity not found: ' . EntityInfo::buildEntityString(
				$this->getEntityModel(), $id));
	}
}
