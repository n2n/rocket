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

namespace rocket\op\ei\manage\frame;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\UnknownEiTypeExtensionException;
use rocket\op\ei\UnknownEiTypeException;
use rocket\op\spec\TypePath;

class EiObjectFactory {

	function __construct(private EiFrame $eiFrame) {
	}


	/**
	 * @return EiEntry[]
	 * @throws UnknownEiTypeExtensionException
	 * @throws UnknownEiTypeException
	 */
	function createPossibleNewEiEntries(?TypePath $contextEiTypePath): array {
		$contextEiType = $contextEiTypePath === null
				? $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()
				: $this->eiFrame->getContextEiEngine()->getEiMask()->determineEiMaskByEiTypePath($contextEiTypePath)
						->getEiType();

		$newEiEntries = [];

		if (!$contextEiType->isAbstract() /* && ($eiTypeIds === null || in_array($contextEiType->getId(), $eiTypeIds)) */) {
			$newEiEntries[$contextEiType->getId()] = $this->eiFrame
					->createEiEntry($contextEiType->createNewEiObject());
		}

		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
			if ($eiType->isAbstract() /*&& ($eiTypeIds === null || in_array($eiType->getId(), $eiTypeIds))*/) {
				continue;
			}

			$newEiEntries[$eiType->getId()] = $this->eiFrame->createEiEntry($eiType->createNewEiObject());
		}

		return $newEiEntries;
	}
}