<?php

namespace rocket\impl\ei\component\prop\adapter\trait;

use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\component\prop\EiPropNature;
use n2n\reflection\property\AccessProxy;
use rocket\op\ei\util\factory\EifField;
use n2n\validation\validator\impl\Validators;

trait WritableEiFieldTrait  {
	use EditConfigTrait, ReadableEiFieldTrait {
		ReadableEiFieldTrait::buildEifField as buildReadableEifField;
	}

	/**
	 * @see EiPropNature::getNativeAccessProxy()
	 */
	abstract function getNativeAccessProxy(): ?AccessProxy;

	protected function buildEifField(Eiu $eiu): ?EifField {
		$eifField = $this->buildReadableEifField($eiu);

		if ($eifField !== null && $eiu->prop()->isNativeWritable() && !$this->isReadOnly()) {
			$eifField->setWriter(function ($value) use ($eiu) {
				$eiu->prop()->writeNativeValue($value);
			});

			$eifField->setCopier(function ($value) {
				return $value;
			});
		}

		return $eifField;
	}

	protected function buildEiFieldValidators(Eiu $eiu): array {
		if ($this->isMandatory()) {
			return [Validators::mandatory()];
		}

		return [];
	}

	/**
	 * @see EiPropNature::buildEiField()
	 */
	function buildEiField(Eiu $eiu): ?EiFieldNature {
		return $this->buildEifField($eiu)?->toEiField();
	}
}