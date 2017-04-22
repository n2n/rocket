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

namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\util\ex\IllegalStateException;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\mapping\OnWriteMappingListener;
use rocket\spec\ei\manage\mapping\WrittenMappingListener;
use rocket\spec\ei\manage\mapping\OnValidateMappingListener;
use rocket\spec\ei\manage\mapping\ValidatedMappingListener;
use rocket\spec\ei\manage\mapping\MappingOperationFailedException;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\GuiException;
use lib\rocket\spec\ei\manage\util\model\GeneralIdUtils;

class EiuEntry {
	private $eiObject;
	private $eiMapping;
	private $eiuFrame;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiObject = $eiuFactory->getEiObject(true);
		$this->eiMapping = $eiuFactory->getEiMapping();
		$this->eiuFrame = $eiuFactory->getEiuFrame(true);
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\spec\ei\manage\util\model\EiUtils
	 */
	public function getEiUtils() {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
	
		throw new IllegalStateException('No EiUtils provided to ' . (new \ReflectionClass($this))->getShortName());
	}
	
	public function hasEiuFrame() {
		return $this->eiuFrame !== null;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function getEiuFrame(bool $required = true) {
		if (!$required || $this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		throw new EiuPerimeterException('No EiuFame provided to ' . (new \ReflectionClass($this))->getShortName());
	}
	
	public function gui($eiObjectGuiObj) {
		return new EiuEntryGui($eiObjectGuiObj, $this);
	}
	
	/**
	 * @param bool $eiObjectObj
	 * @param bool $editable
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuEntryGui
	 */
	public function newGui(bool $overview = false, bool $editable = false) {
		$eiObjectGui = null;
		if (!$overview) {
			$eiObjectGui = $this->getEiuFrame()->getEiMask()->createBulkyEiEntryGui($this, $editable);
		} else if (null === $this->getEiuFrame()->getNestedSetStrategy()) {
			$eiObjectGui = $this->getEiuFrame()->getEiMask()->createListEiEntryGui($this, $editable);
		} else {
			$eiObjectGui = $this->getEiuFrame()->getEiMask()->createTreeEiEntryGui($this, $editable);
		}
		
		return new EiuEntryGui($eiObjectGui, $this);
	}
	
	public function field($eiPropObj) {
		return new EiuField($eiPropObj, $this);
	}
		
	/**
	 * @return \rocket\spec\ei\manage\EiFrame
	 */
	public function getEiFrame() {
		return $this->getEiuFrame()->getEiFrame();
	}
	
	public function getEiMapping(bool $createIfNotAvaialble = true) {
		if ($this->eiMapping !== null) {
			return $this->eiMapping;
		}
		
		if ($createIfNotAvaialble) {
			return $this->eiMapping = $this->getEiuFrame()->createEiMapping($this->eiObject);
		}
		
		return null;
	}
	
	public function getValue($eiPropPath) {
		return $this->getEiMapping()->getValue($eiPropPath);
	}
	
	public function setValue($eiPropPath, $value) {
		return $this->getEiMapping()->setValue($eiPropPath, $value);
	}
	
	public function getValues() {
		$eiMapping = $this->getEiMapping();
		$values = array();
		foreach (array_keys($eiMapping->getMappableWrappers()) as $eiPropPathStr) {
			$values[$eiPropPathStr] = $this->getEiMapping()->getValue($eiPropPathStr);
		}
		return $values;
	}

	/**
	 * @param $eiPropPath
	 * @param $scalarValue
	 * @throws \n2n\reflection\property\ValueIncompatibleWithConstraintsException
	 */
	public function setScalarValue($eiPropPath, $scalarValue) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$scalarEiProperty = $this->getEiuFrame()->getEiMask()->getEiEngine()->getScalarEiDefinition()
				->getScalarEiPropertyByFieldPath($eiPropPath);
		$this->setValue($eiPropPath, $scalarEiProperty->scalarValueToMappableValue($scalarValue));
	}
	
	public function getScalarValue($eiPropPath) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$scalarEiProperty = $this->getEiuFrame()->getEiMask()->getEiEngine()->getScalarEiDefinition()
				->getScalarEiPropertyByFieldPath($eiPropPath);
		return $scalarEiProperty->mappableValueToScalarValue($this->getValue($eiPropPath));
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiObject
	 */
	public function getEiObject() {
		return $this->eiObject;
	}
	
	public function isNew() {
		if ($this->isDraft()) {
			return $this->isDraftNew();
		} else {
			return !$this->isLivePersistent();
		}
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiEntityObj
	 */
	public function getEiEntityObj() {
		return $this->eiObject->getEiEntityObj();
	}
	
	/**
	 * @return boolean
	 */
	public function isLivePersistent() {
		return $this->eiObject->getEiEntityObj()->isPersistent();
	}
	
	public function hasLiveId() {
		return $this->eiObject->getEiEntityObj()->hasId();
	}
	
	/**
	 * @param bool $required
	 * @return mixed
	 */
	public function getLiveId(bool $required = true) {
		$eiEntityObj = $this->getEiEntityObj();
		
		if (!$required && !$eiEntityObj->isPersistent()) {
			return null;
		}
		
		return $eiEntityObj->getId();
	}

	/*
	 * @param bool $required
	 * @return string
	 */
	public function getLiveIdRep(bool $required = true) {
		return $this->getEiType()->idToIdRep($this->getLiveId($required));
	}
	
	/**
	 * @return \rocket\spec\ei\EiType
	 */
	public function getEiType() {
		return $this->getEiEntityObj()->getEiType();
	}
	
	/**
	 * @return boolean
	 */
	public function isDraft() {
		return $this->eiObject->isDraft();
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\spec\ei\manage\draft\Draft
	 */
	public function getDraft(bool $required = true) {
		if (!$required && !$this->isDraft()) {
			return null;
		}
		
		return $this->eiObject->getDraft();
	}
	
	/**
	 * @return boolean
	 */
	public function isDraftNew() {
		return $this->getDraft()->isNew();
	}

	/**
	 * @param bool $required
	 * @return mixed
	 */
	public function getDraftId(bool $required = true) {
		$draft = $this->getDraft();
	
		if (!$required && $draft->isNew()) {
			return null;
		}
	
		return $draft->getId();
	}
	
	/**
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(bool $determineEiMask = true, N2nLocale $n2nLocale = null) {
		return $this->eiuFrame->createIdentityString($this->eiObject, $determineEiMask, $n2nLocale);
	}
	
	/**
	 * @param int $limit
	 * @param int $num
	 * @return \rocket\spec\ei\manage\draft\Draft[]
	 */
	public function lookupDrafts(int $limit = null, int $num = null) {
		return $this->eiuFrame->lookupDraftsByEntityObjId($this->getLiveId(), $limit, $num);
	}
	
	public function acceptsValue($eiPropPath, $value) {
		return $this->getEiMapping()->acceptsValue(EiPropPath::create($eiPropPath), $value);
	}
	
	/**
	 * 
	 * @param unknown $eiPropPath
	 * @param bool $required
	 * @throws MappingOperationFailedException
	 * @return \rocket\spec\ei\manage\mapping\MappableWrapper|null
	 */
	public function getMappableWrapper($eiPropPath, bool $required = false) {
		try {
			return $this->getEiMapping()->getMappableWrapper(EiPropPath::create($eiPropPath));
		} catch (MappingOperationFailedException $e) {
			if ($required) throw $e;
		}
		
		return null;
	}
	
	/**
	 *
	 * @param GuiIdPath $guiIdPath
	 * @param bool $required
	 * @throws MappingOperationFailedException
	 * @throws GuiException
	 * @return \rocket\spec\ei\manage\mapping\MappableWrapper|null
	 */
	public function getMappableWrapperByGuiIdPath($guiIdPath, bool $required = false) {
		$guiDefinition = $this->getEiuFrame()->getEiMask()->getEiEngine()->getGuiDefinition();
		try {
			return $guiDefinition->determineMappableWrapper($this->getEiMapping(), GuiIdPath::createFromExpression($guiIdPath));
		} catch (MappingOperationFailedException $e) {
			if ($required) throw $e;
		} catch (GuiException $e) {
			if ($required) throw $e;
		}
	
		return null;
	}
	
	public function isPreviewAvailable() {
		return !empty($this->eiuFrame->getPreviewTypeOptions($this->eiObject));
	}
	
	public function getPreviewType() {
		return $this->getEiuFrame()->getPreviewType($this->eiObject);
	}
	
	public function getPreviewTypeOptions() {
		return $this->eiuFrame->getPreviewTypeOptions($this->eiObject);
	}
	
	public function isExecutableBy($eiCommandPath) {
		return $this->getEiMapping()->isExecutableBy(EiCommandPath::create($eiCommandPath));
	}
	
	public function onValidate(\Closure $closure) {
		$this->getEiMapping()->registerListener(new OnValidateMappingListener($closure));
	}
	
	public function whenValidated(\Closure $closure) {
		$this->getEiMapping()->registerListener(new ValidatedMappingListener($closure));
	}
	
	public function onWrite(\Closure $closure) {
		$this->getEiMapping()->registerListener(new OnWriteMappingListener($closure));
	}
	
	public function whenWritten(\Closure $closure) {
		$this->getEiMapping()->registerListener(new WrittenMappingListener($closure));
	}
	
	/**
	 * @return NULL|string
	 */
	public function getGeneralId() {
		return GeneralIdUtils::generalIdOf($this->getEiObject());
	}
}  
