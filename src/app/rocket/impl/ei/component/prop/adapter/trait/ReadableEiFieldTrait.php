<?php

namespace rocket\impl\ei\component\prop\adapter\trait;

use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\component\prop\EiPropNature;
use n2n\reflection\property\AccessProxy;
use rocket\op\ei\util\factory\EifField;
use rocket\impl\ei\component\prop\adapter\PropertyNatureTrait;
use n2n\util\type\ArgUtils;
use n2n\validation\validator\Validator;
use n2n\util\type\TypeConstraint;

trait ReadableEiFieldTrait {
	use PropertyNatureTrait;


	protected function buildEifField(Eiu $eiu): ?EifField {
		if (!$eiu->prop()->isNativeReadable()) {
			return null;
		}

		$validators = $this->buildEiFieldValidators($eiu);
		ArgUtils::valArrayReturn($validators, $this, 'buildEiFieldValidators', Validator::class);

		return $eiu->factory()
				->newField($eiu->prop()->getNativeReadTypeConstraint(), function() use ($eiu) {
					return $eiu->prop()->readNativeValue();
				})
				->val(...$validators);
	}

	protected function buildEiFieldValidators(Eiu $eiu): array {
		return [];
	}

	/**
	 * @see EiPropNature::buildEiField()
	 */
	function buildEiField(Eiu $eiu): ?EiFieldNature {
		return $this->buildEifField($eiu)?->toEiField();
	}
}