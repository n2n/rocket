<?php

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
	function createPossibleNewEiEntries(TypePath $eiTypePath): array {
		$contextEiType = $this->eiFrame->getContextEiEngine()->getEiMask()->determineEiMaskByEiTypePath($eiTypePath)
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