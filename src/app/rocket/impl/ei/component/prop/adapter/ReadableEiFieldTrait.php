<?php

namespace rocket\impl\ei\component\prop\adapter;

use rocket\ei\util\Eiu;
use rocket\ei\manage\entry\EiField;
use rocket\ei\component\prop\EiPropNature;
use n2n\reflection\property\AccessProxy;
use rocket\ei\util\factory\EifField;

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
	function buildEiField(Eiu $eiu): ?EiField {
		return $this->buildEifField($eiu)?->toEiField();
	}
}