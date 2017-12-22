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

use n2n\util\ex\IllegalStateException;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\mapping\OnWriteMappingListener;
use rocket\spec\ei\manage\mapping\WrittenMappingListener;
use rocket\spec\ei\manage\mapping\OnValidateMappingListener;
use rocket\spec\ei\manage\mapping\ValidatedMappingListener;
use rocket\spec\ei\manage\mapping\MappingOperationFailedException;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\GuiException;
use rocket\spec\ei\manage\gui\ViewMode;
use rocket\spec\ei\manage\gui\EiGui;

class EiuEntry {
	private $eiObject;
	private $eiEntry;
	private $eiuFrame;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiObject = $eiuFactory->getEiObject(true);
		$this->eiEntry = $eiuFactory->getEiEntry();
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
	
	/**
	 * @return \rocket\spec\ei\mask\EiMask
	 */
	public function determineEiMask() {
		return $this->getEiuFrame()->determineEiMask($this->eiObject);
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
	
	/**
	 * @return boolean
	 */
	public function isAccessible() {
		return $this->eiEntry->isAccessible();
	}
	
	/**
	 * @param bool $eiObjectObj
	 * @param bool $editable
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuEntryGui
	 */
	public function newEntryGui(bool $bulky = true, bool $editable = false, int $treeLevel = null, 
			bool $determineEiMask = true) {
		$eiMask = null;
		if ($determineEiMask) {
			$eiMask = $this->determineEiMask();
		} else {
			$eiMask = $this->getEiFrame()->getContextEiMask();
		}
		
		$viewMode = null;
		if (!$editable) {
			$viewMode = $bulky ? ViewMode::BULKY_READ : ViewMode::COMPACT_READ;
		} else if ($this->isNew()) {
			$viewMode = $bulky ? ViewMode::BULKY_ADD : ViewMode::COMPACT_ADD;
		} else {
			$viewMode = $bulky ? ViewMode::BULKY_EDIT : ViewMode::COMPACT_EDIT;
		}
		
		$eiGui = new EiGui($this->getEiFrame(), $viewMode);
		$eiGui->init($eiMask->createEiGuiViewFactory($eiGui));
		
		return new EiuEntryGui($eiGui->createEiEntryGui($this->getEiEntry(), $treeLevel));
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
	
	public function getEiEntry(bool $createIfNotAvaialble = true) {
		if ($this->eiEntry !== null) {
			return $this->eiEntry;
		}
		
		if ($createIfNotAvaialble) {
			return $this->eiEntry = $this->getEiuFrame()->createEiEntry($this->eiObject);
		}
		
		return null;
	}
	
	public function getValue($eiPropPath) {
		return $this->getEiEntry()->getValue($eiPropPath);
	}
	
	public function setValue($eiPropPath, $value) {
		return $this->getEiEntry()->setValue($eiPropPath, $value);
	}
	
	public function getValues() {
		$eiEntry = $this->getEiEntry();
		$values = array();
		foreach (array_keys($eiEntry->getEiFieldWrappers()) as $eiPropPathStr) {
			$values[$eiPropPathStr] = $this->getEiEntry()->getValue($eiPropPathStr);
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
		$this->setValue($eiPropPath, $scalarEiProperty->scalarValueToEiFieldValue($scalarValue));
	}
	
	public function getScalarValue($eiPropPath) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$scalarEiProperty = $this->getEiuFrame()->getEiMask()->getEiEngine()->getScalarEiDefinition()
				->getScalarEiPropertyByFieldPath($eiPropPath);
		return $scalarEiProperty->eiFieldValueToScalarValue($this->getValue($eiPropPath));
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
	 * @return object
	 */
	public function getEntityObj() {
		return $this->eiObject->getEiEntityObj()->getEntityObj();
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
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericLabel(N2nLocale $n2nLocale = null) {
		return $this->eiuFrame->getGenericLabel($this, $n2nLocale);
	}

	/**
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericPluralLabel(N2nLocale $n2nLocale = null) {
		return $this->eiuFrame->getGenericPluralLabel($this, $n2nLocale);
	}
	
	/**
	 * @return string
	 */
	public function getGenericIconType() {
		return $this->eiuFrame->getGenericIconType($this);
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
		return $this->getEiEntry()->acceptsValue(EiPropPath::create($eiPropPath), $value);
	}
	
	/**
	 * 
	 * @param mixed $eiPropPath
	 * @param bool $required
	 * @throws MappingOperationFailedException
	 * @return \rocket\spec\ei\manage\mapping\EiFieldWrapper|null
	 */
	public function getEiFieldWrapper($eiPropPath, bool $required = false) {
		try {
			return $this->getEiEntry()->getEiFieldWrapper(EiPropPath::create($eiPropPath));
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
	 * @return \rocket\spec\ei\manage\mapping\EiFieldWrapper|null
	 */
	public function getEiFieldWrapperByGuiIdPath($guiIdPath, bool $required = false) {
		$guiDefinition = $this->getEiuFrame()->getEiMask()->getEiEngine()->getGuiDefinition();
		try {
			return $guiDefinition->determineEiFieldWrapper($this->getEiEntry(), GuiIdPath::createFromExpression($guiIdPath));
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
		return $this->getEiEntry()->isExecutableBy(EiCommandPath::create($eiCommandPath));
	}
	
	public function onValidate(\Closure $closure) {
		$this->getEiEntry()->registerListener(new OnValidateMappingListener($closure));
	}
	
	public function whenValidated(\Closure $closure) {
		$this->getEiEntry()->registerListener(new ValidatedMappingListener($closure));
	}
	
	public function onWrite(\Closure $closure) {
		$this->getEiEntry()->registerListener(new OnWriteMappingListener($closure));
	}
	
	public function whenWritten(\Closure $closure) {
		$this->getEiEntry()->registerListener(new WrittenMappingListener($closure));
	}
	
	/**
	 * @return NULL|string
	 */
	public function getGeneralId() {
		return GeneralIdUtils::generalIdOf($this->getEiObject());
	}
}  
