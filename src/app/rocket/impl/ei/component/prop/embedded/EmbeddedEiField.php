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
namespace rocket\impl\ei\component\prop\embedded;

use rocket\impl\ei\component\prop\adapter\entry\EiFieldNatureAdapter;
use n2n\impl\persistence\orm\property\EmbeddedEntityProperty;
use n2n\reflection\ReflectionUtils;
use n2n\util\type\CastUtils;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\entry\EiFieldMap;
use rocket\op\ei\util\entry\EiuFieldMap;
use rocket\op\ei\manage\entry\EiFieldValidationResult;
use n2n\util\type\ArgUtils;
use n2n\l10n\Message;
use rocket\op\ei\manage\entry\EiEntryValidationResult;

class EmbeddedEiField extends EiFieldNatureAdapter {
	private $eiu;
	private $eiProp;
	
	private $forkedEiuFieldMap;
	
	/**
	 * @param Eiu $eiu
	 * @param EmbeddedEiPropNature $eiProp
	 */
	public function __construct(Eiu $eiu, EmbeddedEiPropNature $eiProp) {
		parent::__construct();
		$this->eiu = $eiu;
		$this->eiProp = $eiProp;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldNatureAdapter::checkValue()
	 */
	protected function checkValue(mixed $value): mixed {
		ArgUtils::assertTrue($value === null || $value instanceof EiuFieldMap);
		return $value;
	}
	
	/**
	 * @param object $targetObject
	 * @return \rocket\op\ei\util\entry\EiuFieldMap
	 */
	private function buildEiFieldMap($targetObject) {
		if ($targetObject === null) {
			$targetObject = ReflectionUtils::createObject($this->eiProp->getEntityProperty(true)
					->getEmbeddedEntityPropertyCollection()->getClass());
		}
		
		$efm = $this->eiu->entry()->newFieldMap($this->eiu->prop()->getPath(), $targetObject);
		
		return $efm;
	}
	
	/**
	 * @return \rocket\op\ei\util\entry\EiuFieldMap|null
	 */
	protected function readValue() {
		$targetLiveObject = $this->eiu->entry()->readNativeValue();
		$this->forkedEiuFieldMap = $this->buildEiFieldMap($targetLiveObject);
		
		if ($targetLiveObject !== null) {
			return $this->forkedEiuFieldMap;
		}
		
		return null;
	}
	
	
	protected function isValueValid(mixed $value): bool {
		if ($value === null) return !$this->eiProp->isMandatory();
		
		CastUtils::assertTrue($value instanceof EiuFieldMap);
		
		return $value->isValid();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldNatureAdapter::validateValue()
	 */
	protected function validateValue($value, EiFieldValidationResult $validationResult) {
		if ($value !== null) {
			CastUtils::assertTrue($value instanceof EiuFieldMap);

			$eiEntryValidationResult = new EiEntryValidationResult();
			$validationResult->addSubEiEntryValidationResult($eiEntryValidationResult);
			$value->validate($eiEntryValidationResult);
			return;
		}

		if ($this->eiProp->isMandatory()) {
			$validationResult->addError(Message::createCodeArg('common_field_required_err', 
					['field' => $this->eiProp->getLabelLstr()->t($this->eiu->getN2nLocale())], null, 'rocket'));
		}
	}
	
	public function isWritable(): bool {
		return true;
	}
	
	public function copyEiField(Eiu $copyEiu) {
		return null;
	}
	
	protected function writeValue($value): void {
		if ($value !== null) {
			assert($value instanceof EiuFieldMap);
			$value->write();
			$value = $value->getObject();
		}
		
		$this->eiu->prop()->writeNativeValue($value);
	}

	public function isReadable(): bool {
		return true;
	}
	
	public function hasForkedEiFieldMap(): bool {
		return true;
	}
	
	public function getForkedEiFieldMap(): EiFieldMap {
		if (null !== ($value = $this->getValue())) {
			return $value->getEiFieldMap();
		}
		
		return $this->forkedEiuFieldMap->getEiFieldMap();
	}
	public function copyValue(Eiu $copyEiu) {
	}

	public function isCopyable(): bool {
		return false;
	}

}