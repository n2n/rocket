<?php

namespace rocket\impl\ei\component\prop\adapter;

use rocket\ei\util\Eiu;
use rocket\ei\manage\entry\EiField;
use rocket\ei\component\prop\EiPropNature;
use n2n\reflection\property\AccessProxy;
use n2n\validation\validator\impl\Validators;
use rocket\ei\util\factory\EifField;

trait WritableEiFieldTrait  {
	use ReadableEiFieldTrait {
		ReadableEiFieldTrait::buildEifField as buildReadableEifField;
	}

	/**
	 * @see EiPropNature::getNativeAccessProxy()
	 */
	abstract function getNativeAccessProxy(): ?AccessProxy;

	protected function buildEifField(Eiu $eiu): ?EifField {
		$eifField = $this->buildReadableEifField($eiu);

		if ($eifField !== null && $eiu->prop()->isNativeWritable()) {
			$eifField->setWriter(function ($value) use ($eiu) {
				$eiu->prop()->writeNativeValue($value);
			});

			$eifField->setCopier(function ($value) {
				return $value;
			});
		}

		return $eifField;
	}

	/**
	 * @see EiPropNature::buildEiField()
	 */
	function buildEiField(Eiu $eiu): ?EiField {
		return $this->buildEifField($eiu)?->toEiField();
	}
}