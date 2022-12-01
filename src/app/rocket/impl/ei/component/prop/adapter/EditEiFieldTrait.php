<?php

namespace rocket\impl\ei\component\prop\adapter;

use rocket\ei\util\Eiu;
use rocket\ei\manage\entry\EiField;
use rocket\ei\component\prop\EiPropNature;
use n2n\reflection\property\AccessProxy;
use n2n\validation\validator\impl\Validators;
use rocket\ei\util\factory\EifField;
use rocket\impl\ei\component\prop\adapter\config\EditConfigTrait;
use n2n\util\type\ArgUtils;
use n2n\validation\validator\Validator;

trait EditEiFieldTrait  {
	use EditConfigTrait, WritableEiFieldTrait {
		WritableEiFieldTrait::buildEifField as buildWritableEifField;
	}

	/**
	 * @see EiPropNature::getNativeAccessProxy()
	 */
	abstract function getNativeAccessProxy(): ?AccessProxy;

	protected function buildEifField(Eiu $eiu): ?EifField {
		$eifField = $this->buildWritableEifField($eiu);

		if ($this->isReadOnly()) {
			$eifField->setWriter(null);
			$eifField->setCopier(null);
		}

		$validators = $this->buildEiFieldValidators($eiu);
		ArgUtils::valArrayReturn($validators, $this, 'buildEiFieldValidators', Validator::class);

		$eifField->val(...$validators);

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
	function buildEiField(Eiu $eiu): ?EiField {
		return $this->buildEifField($eiu)?->toEiField();
	}
}