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

use rocket\ei\manage\entry\EiFieldValidationResult;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\util\type\TypeConstraints;
use rocket\ei\util\entry\EiuEntry;
use n2n\validation\impl\ValidationMessages;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;

class ToManyEiField extends EiFieldAdapter {
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
	
	function __construct(Eiu $eiu, RelationEiProp $eiProp, RelationModel $relationModel) {
		parent::__construct(TypeConstraints::array(false, EiuEntry::class));
		
		$this->eiProp = $eiProp;
		$this->eiu = $eiu;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::checkValue()
	 */
	protected function checkValue($value) {
		ArgUtils::assertTrue(is_array($value));
		foreach ($value as $eiuEntry) {
			ArgUtils::assertTrue($eiuEntry instanceof EiuEntry);
			if (!$this->relationModel->getTargetEiuEngine()->type()->test($value)) {
				return false;
			}
		}
		
		return true; 
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::readValue()
	 */
	protected function readValue() {
		$targetEntityObjs = $this->eiu->object()->readNativValue($this->eiProp);
		
		if ($targetEntityObjs === null) {
			return [];
		}
		
		$value = [];
		foreach ($targetEntityObjs as $key => $targetEntityObj) {
			$value[$key] = $this->eiu->frame()->entry($targetEntityObj);
		}
		return $value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::isValueValid()
	 */
	protected function isValueValid($value) {
		$min = $this->relationModel->getMin();
		$max = $this->relationModel->getMax();
		
		return (null === $max || count($value) <= $max) && (null === $min || count($value) >= $min);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::validateValue()
	 */
	protected function validateValue($value, EiFieldValidationResult $validationResult) {
		$min = $this->relationModel->getMin();
		if ($min !== null && $min > count($value)) {
			$validationResult->addError(ValidationMessages::minElements(null, $this->eiu->prop()->getLabel()));
		}
		
		$max = $this->relationModel->getMax();
		if ($max !== null && $max < count($value)) {
			$validationResult->addError(ValidationMessages::maxElements(null, $this->eiu->prop()->getLabel()));
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::isWritable()
	 */
	public function isWritable(): bool {
		return $this->eiu->object()->isNativeWritable($this->eiProp);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::writeValue()
	 */
	protected function writeValue($value) {
		$nativeValue = null;
		if ($value !== null) {
			ArgUtils::assertTrue($value instanceof EiuEntry);
			$nativeValue = $value->getEntityObj();
		}
		
		$this->eiu->object()->writeNativeValue($this->eiProp, $nativeValue);		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::isCopyable()
	 */
	public function isCopyable(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::copyValue()
	 */
	public function copyValue(Eiu $copyEiu) {
		IllegalStateException::assertTrue($this->isCopyable());
		
		$targetEiuEntries = $this->getValue();
		
		if (empty($targetEiuEntries)) return [];
		
		if ($this->relationModel->isSourceMany() && !$this->relationModel->isEmbedded()) {
			return $targetEiuEntries;
		}
		
		$copiedValues = [];
		foreach ($targetEiuEntries as $key => $targetEiuEntry) {
			$copiedValues[$key] = $targetEiuEntry->copy();	
		}
		return $copiedValues;
	}
}