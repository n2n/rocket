<?php

namespace rocket\op\ei\manage\gui\factory;

use n2n\core\container\N2nContext;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\op\ei\manage\entry\EiEntry;

class EiSiEntryQualifierFactory {

	function __construct(private readonly N2nContext $n2nContext) {
	}

	function create(EiEntry $eiEntry, int $viewMode): SiEntryQualifier {
		$idName = null;
		if (!$eiEntry->isNew()) {
			$deterIdNameDefinition = $eiEntry->getEiMask()->getEiEngine()->getIdNameDefinition();
			$idName = $deterIdNameDefinition->createIdentityString($eiEntry->getEiObject(),
					$this->n2nContext, $this->n2nContext->getN2nLocale());
		}

		return new SiEntryQualifier(EiSiEntryIdentifierFactory::create($eiEntry, $viewMode), $idName);
	}
}