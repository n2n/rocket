<?php

namespace rocket\impl\ei\component\prop\adapter;

use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\component\prop\EiPropNature;
use n2n\reflection\property\AccessProxy;
use rocket\op\ei\util\factory\EifField;

trait ReadableEiFieldTrait {

	/**
	 * @see EiPropNature::getNativeAccessProxy()
	 */
	abstract function getNativeAccessProxy(): ?AccessProxy;

	protected function buildEifField(Eiu $eiu): ?EifField {
		if (!$eiu->prop()->isNativeReadable()) {
			return null;
		}

		return $eiu->factory()
				->newField($eiu->prop()->getNativeReadTypeConstraint(), function() use ($eiu) {
					return $eiu->prop()->readNativeValue();
				});
	}

	/**
	 * @see EiPropNature::buildEiField()
	 */
	function buildEiField(Eiu $eiu): ?EiFieldNature {
		return $this->buildEifField($eiu)?->toEiField();
	}
}