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
namespace rocket\impl\ei\component\prop\relation\model;

use rocket\op\ei\manage\entry\EiFieldValidationResult;
use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\entry\EiFieldNatureAdapter;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\util\type\TypeConstraints;
use rocket\op\ei\util\entry\EiuEntry;
use n2n\validation\lang\ValidationMessages;
use n2n\util\type\ArgUtils;
use rocket\op\ei\util\frame\EiuFrame;

class ToManyEiField extends EiFieldNatureAdapter {
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	/**
	 * @var RelationEiProp
	 */
	private $eiProp;
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var EiuFrame
	 */
	private $targetEiuFrame;

	/**
	 * @param Eiu $eiu
	 * @param EiuFrame $targetEiuFrame
	 * @param RelationEiProp $eiProp
	 * @param RelationModel $relationModel
	 */
	function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationEiProp $eiProp, RelationModel $relationModel) {
		parent::__construct(TypeConstraints::array(false, EiuEntry::class));
		
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->eiProp = $eiProp;
		$this->relationModel = $relationModel;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldNatureAdapter::checkValue()
	 */
	protected function checkValue($value) {
		ArgUtils::assertTrue(is_array($value));
		foreach ($value as $eiuEntry) {
			ArgUtils::assertTrue($eiuEntry instanceof EiuEntry);
			if (!$this->relationModel->getTargetEiuEngine()->type()->matches($eiuEntry)) {
				return false;
			}
		}
		
		return true; 
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldNatureAdapter::readValue()
	 */
	protected function readValue() {
		$targetEntityObjs = $this->eiu->object()->readNativeValue($this->eiu->prop()->getEiProp());
		
		if ($targetEntityObjs === null) {
			return [];
		}
		
		$value = [];
		foreach ($targetEntityObjs as $key => $targetEntityObj) {
			$value[$key] = $this->targetEiuFrame->entry($targetEntityObj);
		}
		return $value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldNatureAdapter::isValueValid()
	 */
	protected function isValueValid(mixed $value): bool {
		ArgUtils::assertTrue(is_array($value));
		
		$min = $this->relationModel->getMin();
		$max = $this->relationModel->getMax();
		
		if (!(null === $max || count($value) <= $max) && (null === $min || count($value) >= $min)) {
			return false;
		}
		
		if (!$this->relationModel->isEmbedded() && !$this->relationModel->isIntegrated()) {
			return true;
		}
		
		foreach ($value as $targetEiuEntry) {
			ArgUtils::assertTrue($targetEiuEntry instanceof EiuEntry);
			
			if (!$targetEiuEntry->isValid()) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldNatureAdapter::validateValue()
	 */
	protected function validateValue($value, EiFieldValidationResult $validationResult) {
		$min = $this->relationModel->getMin();
		if ($min !== null && $min > count($value)) {
			$validationResult->addError(ValidationMessages::minElements($min, $this->eiu->prop()->getLabel()));
		}
		
		$max = $this->relationModel->getMax();
		if ($max !== null && $max < count($value)) {
			$validationResult->addError(ValidationMessages::maxElements($max, $this->eiu->prop()->getLabel()));
		}
		
		if (!($this->relationModel->isEmbedded() || $this->relationModel->isIntegrated())) {
			return;
		}
		
		foreach ($value as $targetEiuEntry) {
			ArgUtils::assertTrue($targetEiuEntry instanceof EiuEntry);
			$targetEiuEntry->getEiEntry()->validate();
			$validationResult->addSubEiEntryValidationResult($targetEiuEntry->getEiEntry()->getValidationResult());
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\entry\EiFieldNature::isWritable()
	 */
	public function isWritable(): bool {
		return $this->eiu->object()->isNativeWritable($this->eiu->prop()->getEiProp());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldNatureAdapter::writeValue()
	 */
	protected function writeValue($value) {
		ArgUtils::assertTrue(is_array($value));

		$nativeValues = new \ArrayObject();
		foreach ($value as $eiuEntry) {
			ArgUtils::assertTrue($eiuEntry instanceof EiuEntry);

			$nativeValues->append($eiuEntry->getEntityObj());
			
			if ($this->relationModel->isEmbedded() || $this->relationModel->isIntegrated()) {
				$eiuEntry->getEiEntry()->write();
			}
		}
		
		$this->eiu->object()->writeNativeValue($nativeValues, $this->eiu->prop()->getEiProp());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\entry\EiFieldNature::isCopyable()
	 */
	public function isCopyable(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\entry\EiFieldNature::copyValue()
	 */
	public function copyValue(Eiu $copyEiu) {
		$targetEiuEntries = $this->getValue();
		
		if (empty($targetEiuEntries)) return [];
		
		if ($this->relationModel->isSourceMany() && !$this->relationModel->isEmbedded() 
				&& !$this->relationModel->isIntegrated()) {
			return $targetEiuEntries;
		}
		
		$copiedValues = [];
		foreach ($targetEiuEntries as $key => $targetEiuEntry) {
			$copiedValues[$key] = $targetEiuEntry->copy();	
		}
		return $copiedValues;
	}
}