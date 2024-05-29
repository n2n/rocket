<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\prop\string;

use rocket\op\ei\util\Eiu;
use rocket\si\content\impl\SiFields;
use rocket\op\ei\util\factory\EifGuiField;
use n2n\reflection\property\AccessProxy;
use SebastianBergmann\CodeUnit\Mapper;
use n2n\bind\mapper\impl\Mappers;
use n2n\util\type\ArgUtils;
use n2n\spec\valobj\scalar\StringValueObject;
use n2n\bind\build\impl\Bind;
use n2n\bind\err\BindMismatchException;
use n2n\bind\err\BindTargetException;
use n2n\bind\err\UnresolvableBindableException;
use n2n\bind\err\BindException;
use rocket\op\ei\component\InvalidEiConfigurationException;
use n2n\bind\attribute\impl\Marshal;
use rocket\op\ei\manage\generic\ScalarEiProperty;
use rocket\op\ei\manage\generic\CommonScalarEiProperty;

class StringEiPropNature extends AlphanumericEiPropNature {

	private bool $multiline = false;

	function __construct(?AccessProxy $propertyAccessProxy, private ?string $stringValueObjectTypeName = null) {
		parent::__construct($propertyAccessProxy);

		ArgUtils::assertTrue($this->stringValueObjectTypeName === null
				|| is_subclass_of($this->stringValueObjectTypeName, StringValueObject::class));
	}

	function getStringValueObjectTypeName(): ?string {
		return $this->stringValueObjectTypeName;
	}

	function setStringValueObjectTypeName(?string $stringValueObjectTypeName): void {
		$this->stringValueObjectTypeName = $stringValueObjectTypeName;
	}

	/**
	 * @return bool
	 */
	function isMultiline(): bool {
		return $this->multiline;
	}

	/**
	 * @param bool $multiline
	 */
	function setMultiline(bool $multiline): void {
		$this->multiline = $multiline;
	}

	function createOutEifGuiField(Eiu $eiu): EifGuiField  {
		return $eiu->factory()->newGuiField(
				SiFields::stringOut($eiu->field()->getValue())
						->setMultiline($this->isMultiline())
						->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs()));
	}

	private function marshalValue(null|string|StringValueObject $value, Eiu $eiu): ?string {
		if ($this->stringValueObjectTypeName === null) {
			return $value;
		}

		try {
			return Bind::values($value)->toValue($value)->map(Mappers::marshal())
					->exec($eiu->getN2nContext())->get();
		} catch (BindException $e) {
			throw new InvalidEiConfigurationException('StringEiPropNature for ' . $this->propertyAccessProxy
					. ' was not able to marshal value for StringInSiField.', previous: $e);
		}
	}

	private function unmarshalValue(?string $value, Eiu $eiu): null|string|StringValueObject {
		if ($this->stringValueObjectTypeName === null) {
			return $value;
		}

		try {
			Bind::values($value)->toValue($value)->map(Mappers::unmarshal($this->stringValueObjectTypeName))
					->exec($eiu->getN2nContext());
			return $value;
		} catch (BindException $e) {
			throw new InvalidEiConfigurationException('StringEiPropNature for ' . $this->propertyAccessProxy
					. ' was not able to marshal value for StringInSiField.', previous: $e);
		}
	}

	public function buildScalarEiProperty(Eiu $eiu): ?ScalarEiProperty {
		if ($this->stringValueObjectTypeName === null) {
			return parent::buildScalarEiProperty($eiu);
		}

		return new CommonScalarEiProperty($eiu->prop()->getPath(), $this->getLabelLstr(),
				fn ($value) => $this->marshalValue($value, $eiu),
				fn (?string $scalarValue) => $this->unmarshalValue($scalarValue, $eiu));
	}

	function createInEifGuiField(Eiu $eiu): EifGuiField {
		$siField = SiFields::stringIn($this->marshalValue($eiu->field()->getValue(), $eiu))
				->setMandatory($this->isMandatory())
				->setMinlength($this->getMinlength())
				->setMaxlength($this->getMaxlength())
				->setMultiline($this->isMultiline())
				->setPrefixAddons($this->getPrefixSiCrumbGroups())
				->setSuffixAddons($this->getSuffixSiCrumbGroups())
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
		
		return $eiu->factory()->newGuiField($siField)
				->setSaver(function () use ($siField, $eiu) {
					$eiu->field()->setValue($this->unmarshalValue($siField->getValue(), $eiu));
				});
	}
}
