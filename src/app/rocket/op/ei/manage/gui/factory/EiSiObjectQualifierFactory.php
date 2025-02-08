<?php

namespace rocket\op\ei\manage\gui\factory;

use n2n\core\container\N2nContext;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\EiObject;
use rocket\ui\si\content\SiObjectQualifier;
use rocket\op\ei\mask\EiMask;

class EiSiObjectQualifierFactory {

	function __construct(private readonly N2nContext $n2nContext) {
	}

	function createFromEiEntry(EiEntry $eiEntry): SiObjectQualifier {
//		$idName = null;
////		if (!$eiEntry->isNew()) {
//			$deterIdNameDefinition = $eiEntry->getEiMask()->getEiEngine()->getIdNameDefinition();
//			$idName = $deterIdNameDefinition->createIdentityString($eiEntry->getEiObject(),
//					$this->n2nContext, $this->n2nContext->getN2nLocale());
////		}
//
//		return new SiObjectQualifier(
//				EiSiMaskIdentifierFactory::determineSuperTypeId($eiEntry->getEiType()),
//				$eiEntry->getPid(),
//				$idName);

		return $this->create($eiEntry->getEiObject(), $eiEntry->getEiMask());
	}

	function create(EiObject $eiObject, EiMask $eiMask): SiObjectQualifier {
		$idName = null;
		if (!$eiObject->isNew()) {
			$deterIdNameDefinition = $eiMask->getEiEngine()->getIdNameDefinition();
			$idName = $deterIdNameDefinition->createIdentityString($eiObject,
					$this->n2nContext, $this->n2nContext->getN2nLocale());
		}

		return new SiObjectQualifier(
				EiSiMaskIdentifierFactory::determineSuperTypeId($eiObject->getEiEntityObj()->getEiType()),
				$eiObject->getEiEntityObj()->getPid(),
				$idName);
	}

}