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

namespace rocket\ei\util\entry;

use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\ei\manage\entry\OnWriteMappingListener;
use rocket\ei\manage\entry\WrittenMappingListener;
use rocket\ei\manage\entry\OnValidateMappingListener;
use rocket\ei\manage\entry\ValidatedMappingListener;
use rocket\ei\manage\entry\MappingOperationFailedException;
use rocket\ei\manage\gui\GuiIdPath;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\manage\gui\EiEntryGuiAssembler;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\util\spec\EiuMask;
use rocket\ei\util\gui\EiuEntryGui;
use rocket\ei\util\gui\EiuEntryGuiAssembler;
use rocket\ei\util\frame\EiuFrame;

class EiuEntry {
	private $eiObject;
	private $eiEntry;
	private $eiuFrame;
	private $eiuAnalyst;
	private $eiuEngine;
	private $eiuMask;
	
	public function __construct(EiObject $eiObject, EiEntry $eiEntry = null, EiuFrame $eiuFrame = null, 
			EiuAnalyst $eiuAnalyst = null) {
		$this->eiObject = $eiObject;
		$this->eiEntry = $eiEntry;
		$this->eiuFrame = $eiuFrame;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	public function getEiuFrame(bool $required = true) {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		if ($this->eiuAnalyst !== null) {
			return $this->eiuFrame = $this->eiuAnalyst->getEiuFrame($required);
		}
		
		if ($required) {
			throw new EiuPerimeterException('No EiuFrame available.');
		}
	}
	
	
	
	/**
	 * @return EiuEngine
	 */
	public function getEiuEngine() {
		if ($this->eiuEngine === null) {
			$this->eiuEngine = $this->getEiuFrame()->engine($this->eiObject);
		}
		
		return $this->eiuEngine;
	}
	
	/**
	 * @return EiuMask
	 */
	public function getEiuMask() {
		if ($this->eiuMask === null) {
			$this->eiuMask = $this->getEiuFrame()->mask($this->eiObject);
		}
		
		return $this->eiuMask;
	}
	
	private $eiuEntryAccess;
	
	public function access() {
		if ($this->eiuEntryAccess === null) {
			$this->eiuEntryAccess = new EiuEntryAccess($this->getEiuFrame()->getEiFrame()
					->createEiEntryAccess($this->getEiEntry()), $this);
		}
		
		return $this->eiuEntryAccess;
	}
	
	/**
	 * @return \rocket\ei\manage\EiObject
	 */
	public function getEiObject() {
		return $this->eiObject;
	}
	
	/**
	 * @param bool $createIfNotAvaialble
	 * @return \rocket\ei\manage\entry\EiEntry|NULL
	 */
	public function getEiEntry(bool $createIfNotAvaialble = true) {
		if ($this->eiEntry !== null) {
			return $this->eiEntry;
		}
		
		if ($createIfNotAvaialble) {
			$eiFrame = $this->getEiuFrame(true)->getEiFrame();
			$this->eiEntry =  $eiFrame->createEiEntry($this->eiObject);
			return $this->eiEntry; 
		}
		
		return null;
	}
	
	public function isNew() {
		if ($this->isDraft()) {
			return $this->isDraftNew();
		} else {
			return !$this->isLivePersistent();
		}
	}
		
	/**
	 * @return \rocket\ei\manage\EiEntityObj
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
	public function getLivePid(bool $required = true) {
		if (null !== ($id = $this->getLiveId($required))) {
			return $this->getEiType()->idToPid($id);
		}
		
		return null;
	}
	
	/**
	 * @return \rocket\ei\EiType
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
	 * @return \rocket\ei\manage\draft\Draft
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
	 * @return boolean
	 */
	public function isAccessible() {
		return $this->getEiEntry()->isAccessible();
	}
	
	/**
	 * @param bool $eiObjectObj
	 * @param bool $editable
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	public function newEntryGui(bool $bulky = true, bool $editable = false, int $treeLevel = null, 
			bool $determineEiMask = true) {
		$eiMask = null;
		if ($determineEiMask) {
			$eiMask = $this->determineEiMask();
		} else {
			$eiMask = $this->getEiFrame()->getContextEiEngine()->getEiMask();
		}
		
		$viewMode = $this->deterViewMode($bulky, $editable);
		$eiFrame = $this->getEiuFrame()->getEiFrame();
		
		$eiGui = new EiGui($eiFrame, $viewMode);
		$eiGui->init($eiMask->getDisplayScheme()->createEiGuiViewFactory($eiGui,
				$eiFrame->getManageState()->getDef()->getGuiDefinition($eiMask)));
		
		return new EiuEntryGui($eiGui->createEiEntryGui($this->getEiEntry(), $treeLevel), null, $this->eiuAnalyst);
	}
	
	public function newCustomEntryGui(\Closure $uiFactory, array $guiIdPaths, bool $bulky = true, 
			bool $editable = false, int $treeLevel = null, bool $determineEiMask = true) {
// 		$eiMask = null;
// 		if ($determineEiMask) {
// 			$eiMask = $this->determineEiMask();
// 		} else {
// 			$eiMask = $this->getEiFrame()->getContextEiEngine()->getEiMask();
// 		}
		
		$viewMode = $this->deterViewMode($bulky, $editable);
		$eiuGui = $this->eiuFrame->newCustomGui($viewMode, $uiFactory, $guiIdPaths);
		return $eiuGui->appendNewEntryGui($this, $treeLevel);
	}
	
	/**
	 * @param int $viewMode
	 * @param bool $determineEiMask
	 * @return \rocket\ei\util\gui\EiuEntryGuiAssembler
	 */
	public function newEntryGuiAssembler(int $viewMode, bool $determineEiMask = true) {
		$eiFrame = $this->getEiuFrame()->getEiFrame();
		$eiMask = null;
		if ($determineEiMask) {
			$eiMask = $eiFrame->determineEiMask($this->eiObject->getEiEntityObj()->getEiType());
		} else {
			$eiMask = $eiFrame->getContextEiEngine()->getEiMask();
		}
		
		$eiGui = new EiGui($eiFrame, $viewMode);
		$eiGui->init($eiMask->getDisplayScheme()->createEiGuiViewFactory($eiGui, 
				$eiFrame->getManageState()->getDef()->getGuiDefinition($eiMask)));
		$eiEntryGuiAssembler = new EiEntryGuiAssembler(new EiEntryGui($eiGui, $this->eiEntry));
		
// 		if ($parentEiEntryGui->isInitialized()) {
// 			throw new \InvalidArgumentException('Parent EiEntryGui already initialized.');
// 		}
		
// 		$parentEiEntryGui->registerEiEntryGuiListener(new InitListener($eiEntryGuiAssembler));
		
		return new EiuEntryGuiAssembler($eiEntryGuiAssembler, null, $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	private function determineEiMask() {
		return $this->eiuFrame->getEiFrame()->determineEiMask($this->eiObject->getEiEntityObj()->getEiType());
	}
	
	/**
	 * @param bool $bulky
	 * @param bool $editable
	 * @return int
	 */
	public function deterViewMode(bool $bulky, bool $editable) {
		if (!$editable) {
			return $bulky ? ViewMode::BULKY_READ : ViewMode::COMPACT_READ;
		} else if ($this->isNew()) {
			return $bulky ? ViewMode::BULKY_ADD : ViewMode::COMPACT_ADD;
		} else {
			return $bulky ? ViewMode::BULKY_EDIT : ViewMode::COMPACT_EDIT;
		}
	}
	
	/**
	 * @param mixed $eiPropArg
	 * @return \rocket\ei\util\entry\EiuField
	 */
	public function field($eiPropArg) {
		return new EiuField($eiPropArg, $this);
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
		$scalarEiProperty = $this->getEiuFrame()->getContextEiuEngine()->getScalarEiProperty($eiPropPath);
		$this->setValue($eiPropPath, $scalarEiProperty->scalarValueToEiFieldValue($scalarValue));
	}
	
	public function getScalarValue($eiPropPath) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$scalarEiProperty = $this->getEiuFrame()->getContextEiuEngine()->getScalarEiProperty($eiPropPath);
		return $scalarEiProperty->eiFieldValueToScalarValue($this->getValue($eiPropPath));
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
	 * @param bool $draft
	 * @param mixed $eiTypeArg
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	public function copy(bool $draft = null, $eiTypeArg = null) {
		return $this->eiuFrame->copyEntry($this, $draft, $eiTypeArg);
	}
	
	public function copyValuesTo($toEiEntryArg, array $eiPropPaths = null) {
		$this->eiuFrame->copyEntryValuesTo($this, $toEiEntryArg, $eiPropPaths);
	}
	
	/**
	 * @return \rocket\ei\EiEngine
	 */
	public function getEiEngine() {
		return $this->eiuFrame->determineEiEngine($this);
	}
	
// 	/**
// 	 * @param mixed $guiIdPath
// 	 * @return boolean
// 	 */
// 	public function containsGuiProp($guiIdPath) {
// 		return $this->eiuFrame->containsGuiProp($guiIdPath);
// 	}
	
// 	/**
// 	 * @param GuiIdPath|string $guiIdPath
// 	 * @return \rocket\ei\EiPropPath|null
// 	 */
// 	public function guiIdPathToEiPropPath($guiIdPath) {
// 		return $this->eiuFrame->guiIdPathToEiPropPath($guiIdPath, $this);
// 	}
	
	/**
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(bool $determineEiMask = true, N2nLocale $n2nLocale = null) {
		return $this->getEiuFrame()->createIdentityString($this->eiObject, $determineEiMask, $n2nLocale);
	}
	
	/**
	 * @param int $limit
	 * @param int $num
	 * @return \rocket\ei\manage\draft\Draft[]
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
	 * @return \rocket\ei\manage\entry\EiFieldWrapper|null
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
	 * @param GuiIdPath $guiIdPath
	 * @param bool $required
	 * @throws MappingOperationFailedException
	 * @throws GuiException
	 * @return \rocket\ei\manage\entry\EiFieldWrapper|null
	 */
	public function getEiFieldWrapperByGuiIdPath($guiIdPath, bool $required = false) {
		$guiDefinition = $this->getEiuFrame()->getContextEiuEngine()->getGuiDefinition();
		try {
			return $guiDefinition->determineEiFieldWrapper($this->getEiEntry(), GuiIdPath::create($guiIdPath));
		} catch (MappingOperationFailedException $e) {
			if ($required) throw $e;
		} catch (GuiException $e) {
			if ($required) throw $e;
		}
	
		return null;
	}
	
	/**
	 * @param mixed $eiTypeArg
	 * @return boolean
	 */
	public function isTypeOf($eiTypeArg) {
		$eiType = EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg, 'eiTypeArg');
		
		return $this->getEiType()->equals($eiType);
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
	
// 	public function isExecutableBy($eiCommandPath) {
// 		return $this->getEiEntry()->isExecutableBy(EiCommandPath::create($eiCommandPath));
// 	}
	
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

// class InitListener implements EiEntryGuiListener {
// 	private $eiEntryGuiAssembler;
	
// 	public function __construct(EiEntryGuiAssembler $eiEntryGuiAssembler) {
// 		$this->eiEntryGuiAssembler = $eiEntryGuiAssembler;
// 	}
	
// 	public function finalized(EiEntryGui $eiEntryGui) {
// 		$eiEntryGui->unregisterEiEntryGuiListener($this);
		
// 		$this->eiEntryGuiAssembler->finalize();
// 	}

// 	public function onSave(EiEntryGui $eiEntryGui) {
// 	}

// 	public function saved(EiEntryGui $eiEntryGui) {
// 	}
// }
