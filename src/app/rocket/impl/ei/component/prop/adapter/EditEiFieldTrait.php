<?php
//
//namespace rocket\impl\ei\component\prop\adapter;
//
//use rocket\op\ei\util\Eiu;
//use rocket\op\ei\manage\entry\EiFieldNature;
//use rocket\op\ei\component\prop\EiPropNature;
//use n2n\reflection\property\AccessProxy;
//use n2n\validation\validator\impl\Validators;
//use rocket\op\ei\util\factory\EifField;
//use n2n\util\type\ArgUtils;
//use n2n\validation\validator\Validator;
//use rocket\impl\ei\component\prop\adapter\trait\WritableEiFieldTrait;
//
//trait EditEiFieldTrait  {
//	use WritableEiFieldTrait {
//		WritableEiFieldTrait::buildEifField as buildWritableEifField;
//	}
//
//	/**
//	 * @see EiPropNature::getNativeAccessProxy()
//	 */
//	abstract function getNativeAccessProxy(): ?AccessProxy;
//
//	protected function buildEifField(Eiu $eiu): ?EifField {
//		$eifField = $this->buildWritableEifField($eiu);
//
//		if ($this->isReadOnly()) {
//			$eifField->setWriter(null);
//			$eifField->setCopier(null);
//		}
//
//		$validators = $this->buildEiFieldValidators($eiu);
//		ArgUtils::valArrayReturn($validators, $this, 'buildEiFieldValidators', Validator::class);
//
//		$eifField->val(...$validators);
//
//		return $eifField;
//	}
//
//
//	/**
//	 * @see EiPropNature::buildEiField()
//	 */
//	function buildEiField(Eiu $eiu): ?EiFieldNature {
//		return $this->buildEifField($eiu)?->toEiField();
//	}
//}