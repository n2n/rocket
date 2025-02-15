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

namespace rocket\op\ei\util\entry;

use n2n\l10n\N2nLocale;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\entry\OnWriteMappingListener;
use rocket\op\ei\manage\entry\WrittenMappingListener;
use rocket\op\ei\manage\entry\OnValidateMappingListener;
use rocket\op\ei\manage\entry\ValidatedMappingListener;
use rocket\op\ei\manage\entry\EiFieldOperationFailedException;
use rocket\op\ei\manage\DefPropPath;
use rocket\ui\gui\ViewMode;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\util\spec\EiuMask;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\entry\UnknownEiFieldExcpetion;
use rocket\op\ei\component\prop\EiPropNature;
use rocket\op\ei\component\prop\EiProp;
use rocket\op\launch\TransactionApproveAttempt;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\op\ei\util\gui\EiuGuiEntry;
use rocket\op\ei\manage\frame\EiObjectSelector;
use rocket\op\ei\manage\gui\EiFieldAbstraction;
use rocket\op\ei\util\spec\EiuProp;
use rocket\ui\gui\impl\BulkyGui;
use rocket\op\ei\manage\gui\factory\EiGuiFactory;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use rocket\op\ei\util\EiuPerimeterException;
use rocket\op\ei\manage\security\InaccessibleEiFieldException;
use rocket\ui\gui\GuiValueBoundary;
use rocket\op\ei\manage\gui\factory\EiGuiValueBoundaryFactory;
use rocket\ui\gui\impl\CompactGui;
use rocket\op\ei\manage\gui\factory\EiSiEntryQualifierFactory;
use rocket\ui\si\content\SiObjectQualifier;
use rocket\op\ei\manage\gui\factory\EiSiObjectQualifierFactory;


class EiuEntry {
	private ?EiEntry $eiEntry;
	private $eiuAnalyst;
	private $eiuObject;
	private $eiuMask;
	
	/**
	 * @param EiEntry|null $eiEntry
	 * @param EiuObject|null $eiuObject
	 * @param EiuMask|null $eiuMask
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(?EiEntry $eiEntry, ?EiuObject $eiuObject, ?EiuMask $eiuMask, EiuAnalyst $eiuAnalyst) {
		ArgUtils::assertTrue($eiEntry !== null || $eiuObject !== null);
		$this->eiEntry = $eiEntry;
		$this->eiuObject = $eiuObject;
		$this->eiuMask = $eiuMask;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @param bool $required
	 * @return EiuFrame
	 */
	private function getEiuFrame(bool $required = true): EiuFrame {
		return $this->eiuAnalyst->getEiuFrame($required);
	}
	
	/**
	 * @return EiuMask
	 */
	public function mask() {
		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		if ($this->eiEntry !== null) {
			return $this->eiuMask = new EiuMask($this->eiEntry->getEiMask(), null, $this->eiuAnalyst);
		}
		
		
		if (null !== ($eiFrame = $this->eiuAnalyst->getEiFrame(false))) {
			return $this->eiuMask = new EiuMask(
					$eiFrame->getContextEiEngine()->getEiMask()->determineEiMask($this->eiuObject->getEiType()), 
					null, $this->eiuAnalyst);
		}
		
		return $this->eiuMask = new EiuMask($this->eiuObject->getEiType()->getEiMask(), null, $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\op\ei\util\entry\EiuObject
	 */
	public function object() {
		if ($this->eiuObject !== null) {
			return $this->eiuObject;
		}
		
		return $this->eiuObject = new EiuObject($this->eiEntry->getEiObject(), $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\op\ei\manage\EiObject
	 */
	private function getEiObject() {
		if ($this->eiuObject !== null) {
			return $this->eiuObject->getEiObject();
		}
		
		return $this->eiEntry->getEiObject();
	}
	
	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->eiEntry !== null;
	}
	
	/**
	 * @return \rocket\op\ei\manage\entry\EiEntry|NULL
	 */
	public function getEiEntry(bool $createdIfNotAvailable = true) {
		if ($this->eiEntry !== null) {
			return $this->eiEntry;
		}
		
		if (!$createdIfNotAvailable) {
			return null;
		}
				
		return $this->eiEntry = $this->eiuAnalyst->getEiFrame(false)
				->createEiEntry($this->getEiObject());
	}
	
	/**
	 * @return boolean
	 */
	public function isNew() {
		if ($this->isDraft()) {
			return $this->isDraftNew();
		} else {
			return !$this->isPersistent();
		}
	}
		
	/**
	 * @return \rocket\op\ei\manage\EiEntityObj
	 */
	public function getEiEntityObj() {
		return $this->getEiObject()->getEiEntityObj();
	}
	
	/**
	 * @return object
	 */
	public function getEntityObj() {
		return $this->getEiObject()->getEiEntityObj()->getEntityObj();
	}
	
	/**
	 * @return boolean
	 */
	public function isPersistent() {
		return $this->getEiObject()->getEiEntityObj()->isPersistent();
	}
	
	public function hasId() {
		return $this->getEiObject()->getEiEntityObj()->hasId();
	}
	
	/**
	 * @param bool $required
	 * @return mixed
	 */
	public function getId(bool $required = true) {
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
	public function getPid(bool $required = true) {
		if (null !== ($id = $this->getId($required))) {
			return $this->getEiType()->idToPid($id);
		}
		
		return null;
	}
	
	/**
	 * @return \rocket\op\ei\EiType
	 */
	public function getEiType() {
		return $this->getEiEntityObj()->getEiType();
	}
	
	/**
	 * @return boolean
	 */
	public function isDraft() {
		return $this->getEiObject()->isDraft();
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\op\ei\manage\draft\Draft
	 */
	public function getDraft(bool $required = true) {
		if (!$required && !$this->isDraft()) {
			return null;
		}
		
		return $this->eiEntry->getEiObject()->getDraft();
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
	public function isPreviewSupported() {
		return $this->getEiuFrame()->isPreviewSupported($this);
	}
	
	/**
	 * @param string $previewType
	 * @return string[]
	 */
	public function getPreviewTypeOptions() {
		return $this->getEiuFrame()->getPreviewTypeOptions($this);
	}
	
	/**
	 * @return string|null
	 */
	public function getDefaultPreviewType() {
		return $this->getEiuFrame()->getDefaultPreviewType($this);
	}
	
	/**
	 * @var boolean
	 */
	private $accessible;
	
	/**
	 * @return boolean
	 */
	public function isAccessible(): bool {
		if (null !== $this->accessible) return $this->accessible;
		
		if ($this->eiEntry !== null) {
			return $this->accessible = true;
		}
		
		// @todo check exception and make $this->accessible = false if thrown.
		$this->getEiEntry(true);
		return $this->accessible = true;
	}

	function createGuiValueBoundary(int $viewMode, ?int $treeLevel = null): GuiValueBoundary {
		$eiGuiEntryFactory = new EiGuiValueBoundaryFactory($this->eiuAnalyst->getEiFrame(true));
		return $eiGuiEntryFactory->create($treeLevel, [$this->eiEntry], $viewMode);
	}

//	function newGuiValueBoundary(bool $bulky = true, bool $readOnly = true, bool $entryGuiControlsIncluded = false,
//			?array $defPropPathsArg = null, bool $contextEiMaskUsed = false, ?int $treeLevel = null): EiuGuiValueBoundary {
//		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
//		$eiFrameUtil = new EiObjectSelector($this->eiuAnalyst->getEiFrame(true));
//
//		$eiMaskId = $contextEiMaskUsed
//				? (string) $eiFrameUtil->getEiFrame()->getContextEiEngine()->getEiTypePath()
//				: null;
//
//		$eiGuiValueBoundary = $eiFrameUtil->createEiGuiValueBoundaryFromEiEntry($this->getEiEntry(), $bulky, $readOnly,
//				$entryGuiControlsIncluded, $eiMaskId, $defPropPaths, $treeLevel);
//
//		return new EiuGuiValueBoundary($eiGuiValueBoundary, null, $this->eiuAnalyst);
//	}
//
//	/**
//	 * @param bool $bulky
//	 * @param bool $readOnly
//	 * @param bool $entryGuiControlsIncluded
//	 * @param array|null $defPropPathsArg
//	 * @param bool $contextEiMaskUsed
//	 * @return EiuGuiEntry
//	 */
//	function newGuiEntry(bool $bulky = true, bool $readOnly = true, bool $entryGuiControlsIncluded = false,
//			?array $defPropPathsArg = null, bool $contextEiMaskUsed = false): EiuGuiEntry {
//		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
//		$eiFrameUtil = new EiObjectSelector($this->eiuAnalyst->getEiFrame(true));
//
//		$eiMaskId = $contextEiMaskUsed
//				? (string) $eiFrameUtil->getEiFrame()->getContextEiEngine()->getEiTypePath()
//				: null;
//
//		$eiGuiEntry = $eiFrameUtil->createEiGuiEntry($this->eiEntry, $bulky, $readOnly,
//				$entryGuiControlsIncluded, $eiMaskId, $defPropPaths);
//
//		return new EiuGuiEntry($eiGuiEntry, $this, null, $this->eiuAnalyst);
//	}
	
// 	/**
// 	 * @param bool $eiObjectObj
// 	 * @param bool $editable
// 	 * @throws EiuPerimeterException
// 	 * @return \rocket\op\ei\util\gui\EiuGuiEntry
// 	 */
// 	public function newEntryGui(bool $bulky = true, bool $editable = false, ?int $treeLevel = null,
// 			bool $determineEiMask = true) {
// 		$eiEntry = $this->getEiEntry(true);
// 		$eiEngine = null;
// 		if ($determineEiMask) {
// 			$eiEngine = $eiEntry->getEiMask()->getEiEngine();
// 		} else {
// 			$eiEngine = $this->getEiFrame()->getContextEiEngine();
// 		}
		
// 		$viewMode = $this->deterViewMode($bulky, $editable);
// 		$eiFrame = $this->getEiuFrame()->getEiFrame();
		
// 		$eiGuiMaskDeclaration = $eiFrame->getEiLaunch()->getDef()->getEiGuiDefinition($eiEngine->getEiMask())
// 				->createEiGuiMaskDeclaration($eiFrame, $viewMode);
		
// 		return new EiuGuiEntry($eiGuiMaskDeclaration->createEiGuiValueBoundary($eiEntry, $treeLevel), null, $this->eiuAnalyst);
// 	}
	
// 	/**
// 	 * @param int $viewMode
// 	 * @param bool $determineEiMask
// 	 * @return \rocket\op\ei\util\gui\EiuGuiEntryAssembler
// 	 */
// 	public function newEntryGuiAssembler(int $viewMode, bool $determineEiMask = true) {
// 		$eiFrame = $this->getEiuFrame()->getEiFrame();
// 		$eiMask = null;
// 		if ($determineEiMask) {
// 			$eiMask = $eiFrame->determineEiMask($this->eiEntry->getEiObject()->getEiEntityObj()->getEiType());
// 		} else {
// 			$eiMask = $eiFrame->getContextEiEngine()->getEiMask();
// 		}
		
// 		$eiGuiMaskDeclaration = $eiMask->createEiGuiMaskDeclaration($eiFrame, $viewMode, false);
// 		$eiGuiMaskDeclaration->init(new DummyEiGuiSiFactory(), $eiGuiMaskDeclaration->getEiGuiDefinition()->getDefPropPaths());
		
// 		$eiGuiValueBoundaryAssembler = new EiGuiValueBoundaryAssembler(new EiGuiValueBoundary($eiGuiMaskDeclaration, $this->eiEntry));
		
// // 		if ($parentEiGuiValueBoundary->isInitialized()) {
// // 			throw new \InvalidArgumentException('Parent EiGuiValueBoundary already initialized.');
// // 		}
		
// // 		$parentEiGuiValueBoundary->registerEiGuiValueBoundaryListener(new InitListener($eiGuiValueBoundaryAssembler));
		
// 		return new EiuGuiEntryAssembler($eiGuiValueBoundaryAssembler, null, $this->eiuAnalyst);
// 	}
	
// 	/**
// 	 * @return \rocket\op\ei\mask\EiMask
// 	 */
// 	private function determineEiMask() {
// 		return $this->eiuFrame->getEiFrame()->determineEiMask($this->eiEntry->getEiObject()->getEiEntityObj()->getEiType());
// 	}
	
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
	 * @param string|EiPropPath|EiProp|EiPropNature $eiPropArg
	 * @return boolean
	 */
	public function isFieldWritable($eiPropArg) {
		return $this->eiEntry->getEiEntryAccess()->isEiPropWritable(EiPropPath::create($eiPropArg));
	}
	
	/**
	 * @param mixed $eiPropArg
	 * @return \rocket\op\ei\util\entry\EiuField
	 */
	public function field($eiPropArg) {
		return new EiuField(EiPropPath::create($eiPropArg), $this, $this->eiuAnalyst);
	}
	
	public function getValue($eiPropPath) {
		return $this->getEiEntry()->getValue(EiPropPath::create($eiPropPath));
	}
	
	public function setValue($eiPropPath, $value): static {
		try {
			$this->getEiEntry()->setValue(EiPropPath::create($eiPropPath), $value);
		} catch (ValueIncompatibleWithConstraintsException|InaccessibleEiFieldException $e) {
			throw new EiuPerimeterException('Could not write value to EiField "' . $eiPropPath
					. '" of ' . $this->getEiEntry(), previous: $e);
		}

		return $this;
	}
	
//	public function getValues() {
//		$eiEntry = $this->getEiEntry();
//		$values = array();
//		foreach (array_keys($eiEntry->getEiFieldWrappers()) as $eiPropPathStr) {
//			$values[$eiPropPathStr] = $this->getEiEntry()->getValue($eiPropPathStr);
//		}
//		return $values;
//	}
	
	/**
	 * @param $eiPropPath
	 * @param $scalarValue
	 * @throws \n2n\util\type\ValueIncompatibleWithConstraintsException
	 */
	public function setScalarValue($eiPropPath, $scalarValue) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$scalarEiProperty = $this->getEiuFrame()->contextEngine()->getScalarEiProperty($eiPropPath);
		$this->setValue($eiPropPath, $scalarEiProperty->scalarValueToEiFieldValue($scalarValue));
	}
	
	public function getScalarValue($eiPropPath) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$scalarEiProperty = $this->getEiuFrame()->contextEngine()->getScalarEiProperty($eiPropPath);
		return $scalarEiProperty->eiFieldValueToScalarValue($this->getValue($eiPropPath));
	}
	
	
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericLabel(?N2nLocale $n2nLocale = null) {
		return $this->mask()->getEiMask()->getLabelLstr()
				->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}

	/**
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericPluralLabel(?N2nLocale $n2nLocale = null) {
		return $this->mask()->getEiMask()->getPluralLabelLstr()
				->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	/**
	 * @return string
	 */
	public function getGenericIconType() {
		return $this->getEiuFrame()->getGenericIconType($this);
	}
	
	/**
	 * @param bool $draft
	 * @param mixed $eiTypeArg
	 * @return \rocket\op\ei\util\entry\EiuEntry
	 */
	public function copy(?bool $draft = null, $eiTypeArg = null) {
		return $this->getEiuFrame()->copyEntry($this, $draft, $eiTypeArg);
	}
	
	public function copyValuesTo($toEiEntryArg, ?array $eiPropPaths = null) {
		$this->getEiuFrame()->copyEntryValuesTo($this, $toEiEntryArg, $eiPropPaths);
	}
	
//	/**
//	 * @return \rocket\op\ei\EiEngine
//	 */
//	public function getEiEngine() {
//		return $this->getEiuFrame()->determineEiEngine($this);
//	}
	
// 	/**
// 	 * @param mixed $eiPropPath
// 	 * @return boolean
// 	 */
// 	public function containsGuiProp($eiPropPath) {
// 		return $this->eiuFrame->containsGuiProp($eiPropPath);
// 	}
	
// 	/**
// 	 * @param DefPropPath|string $eiPropPath
// 	 * @return \rocket\op\ei\EiPropPath|null
// 	 */
// 	public function eiPropPathToEiPropPath($eiPropPath) {
// 		return $this->eiuFrame->eiPropPathToEiPropPath($eiPropPath, $this);
// 	}
	
	/**
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(?N2nLocale $n2nLocale = null) {
		return $this->mask()->engine()->createIdentityString($this, true, $n2nLocale);
	}
	
//	/**
//	 * @param int $limit
//	 * @param int $num
//	 * @return \rocket\op\ei\manage\draft\Draft[]
//	 */
//	public function lookupDrafts(?int $limit = null, ?int $num = null) {
//		return $this->eiuFrame->lookupDraftsByEntityObjId($this->getId(), $limit, $num);
//	}
	
	public function acceptsValue($eiPropPath, $value) {
		return $this->getEiEntry()->acceptsValue(EiPropPath::create($eiPropPath), $value);
	}
	
	/**
	 * 
	 * @param mixed $eiPropPath
	 * @param bool $required
	 * @throws UnknownEiFieldExcpetion
	 * @return \rocket\op\ei\manage\entry\EiField|null
	 */
	public function getEiFieldWrapper($eiPropPath, bool $required = false) {
		try {
			return $this->getEiEntry(true)->getEiField(EiPropPath::create($eiPropPath));
		} catch (UnknownEiFieldExcpetion $e) {
			if ($required) throw $e;
		}
		
		return null;
	}
	
	/**
	 * @param DefPropPath|string|EiPropPath|array $defPropPath
	 * @param bool $required
	 * @return EiFieldAbstraction|null
	 * @throws GuiException
	 * @throws EiFieldOperationFailedException
	 */
	public function getEiFieldAbstraction($defPropPath, bool $required = false) {
		$viewMode = $this->eiuAnalyst->getEiuGuiDefinition(false)?->getViewMode() ?? ViewMode::BULKY_EDIT;
		$guiDefinition = $this->eiEntry->getEiMask()->getEiEngine()->getEiGuiDefinition($viewMode);
		try {
			return $guiDefinition->determineEiFieldAbstraction($this->eiuAnalyst->getN2nContext(true),
					$this->getEiEntry(), DefPropPath::create($defPropPath));
		} catch (UnknownEiFieldExcpetion $e) {
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
		
		return $this->getEiType()->isA($eiType);
	}
	
	
// 	public function isExecutableBy($eiCmdPath) {
// 		return $this->getEiEntry()->isExecutableBy(EiCmdPath::create($eiCmdPath));
// 	}
	
	public function onValidate(\Closure $closure): void {
		$this->getEiEntry()->registerListener(new OnValidateMappingListener($closure, $this->eiuAnalyst->getN2nContext(true)));
	}
	
	public function whenValidated(\Closure $closure): void {
		$this->getEiEntry()->registerListener(new ValidatedMappingListener($closure));
	}
	
	public function onWrite(\Closure $closure): void {
		$this->getEiEntry()->registerListener(new OnWriteMappingListener($closure));
	}

	function onWriteOnce(\Closure $closure): void {
		$eiEntry = $this->getEiEntry();
		$listener = new OnWriteMappingListener(null);
		$listener->closure = function () use ($eiEntry, $listener, $closure) {
			$eiEntry->unregisterListener($listener);
			$closure->__invoke($this);
		};

		$this->getEiEntry()->registerListener($listener);
	}
	
	public function whenWritten(\Closure $closure): void {
		$this->getEiEntry()->registerListener(new WrittenMappingListener($closure));
	}
	
	
	public function fieldMap($forkEiPropPath = null): EiuFieldMap {
		$forkEiPropPath = EiPropPath::create($forkEiPropPath);
		$eiFieldMap = $this->eiEntry->getEiFieldMap();
		
		$ids = $forkEiPropPath->toArray();
		while (null !== ($id = array_shift($ids))) {
			$eiFieldMap = $eiFieldMap->getNature($id)->getForkedEiFieldMap();
		}
		return new EiuFieldMap($eiFieldMap, $this->eiuAnalyst);
	}
	
	/**
	 * @param mixed $forkEiPropPath
	 * @param object $object
	 * @return \rocket\op\ei\util\entry\EiuFieldMap
	 */
	public function newFieldMap($forkEiPropPath, object $object) {
		return $this->getEiuFrame()->newFieldMap($this, $forkEiPropPath, $object);
	}
	
//	/**
//	 * @param EiPropNature $eiProp
//	 * @return boolean
//	 */
//	public function isDraftProp($eiPropPath) {
//		$eiPropPath = EiPropPath::create($eiPropPath);
//
//		return $this->getEiObject()->isDraft()
//				&& $this->getEiEntry(true)->getEiMask()->getEiEngine()->getDraftDefinition()
//						->containsEiPropPath($eiPropPath);
//	}
	
	/**
	 * @param EiPropNature $eiProp
	 * @return object
	 */
	public function getForkObject($eiPropPath) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		
		if ($this->isInitialized()) {
			return $this->getEiFieldWrapper($eiPropPath)->getEiFieldMap()->getObject();
		}
			
		return $this->getEiEntry(true)->getEiMask()
				->getForkObject($eiPropPath->poped(), $this->eiEntry->getEiObject());
	}

	/**
	 * @param EiPropPath|EiuProp|EiProp|array|string|null $eiPropPath
	 * @return mixed
	 * @throws EiFieldOperationFailedException
	 */
	public function readNativeValue(EiPropPath|EiuProp|EiProp|array|string|null $eiPropPath = null): mixed {
		$eiPropPath = EiPropPath::build($eiPropPath) ?? $this->eiuAnalyst->getEiPropPath(true);
		
//		if ($this->isDraftProp($eiPropPath)) {
//			return $this->getEiObject()->getDraft()->getDraftValueMap()->getValue($eiPropPath);
//		}

		$eiProp = $this->getEiEntry(true)->getEiMask()->getEiPropCollection()->getByPath($eiPropPath);
		$propertyAccessProxy = $eiProp->getNature()->getNativeAccessProxy();
		if ($propertyAccessProxy !== null) {
			return $propertyAccessProxy->getValue($this->getForkObject($eiPropPath));
		}
		
		throw new EiFieldOperationFailedException('There is no PropertyAccessProxy configured for ' . $eiProp);
	}
	
	/**
	 * @param EiProp $eiProp
	 * @param mixed $value
	 * @throws EiFieldOperationFailedException
	 */
	public function writeNativeValue(EiProp $eiProp, mixed $value): void {
		$eiPropPath = $eiProp->getEiPropPath();
		
//		if ($this->isDraftProp($eiProp)) {
//			$this->eiObject->getDraft()->getDraftValueMap()->setValue($eiPropPath);
//			return;
//		}
		
		$propertyAccessProxy = $eiProp->getNature()->getPropertyAccessProxy();
		if ($propertyAccessProxy !== null) {
			$propertyAccessProxy->setValue($this->getForkObject($eiProp), $value);
			return;
		}
		
		throw new EiFieldOperationFailedException('There is no PropertyAccessProxy configured for ' . $eiProp);
	}
	
	/**
	 * @return boolean
	 */
	function isValid() {
		return $this->getEiEntry()->isValid();
	}
	
	/**
	 * @return boolean
	 */
	function isUnsaved() {
		return $this->eiEntry !== null && $this->eiEntry->hasChanges();
	}
	
	/**
	 * @return boolean
	 */
	function save(/*bool $insertIfNew = false*/) {
		if (!$this->eiEntry->save()) {
			return false;
		}
		
		if (!$this->eiEntry->isNew()) {
			return true;
		}
		
		$eiEntityObj = $this->eiEntry->getEiObject()->getEiEntityObj();
		$nestedSetStrategy = $this->eiEntry->getEiType()->getNestedSetStrategy();
		$em = $this->eiuAnalyst->getEiFrame(true)->getEiLaunch()->getEntityManager();
		if ($nestedSetStrategy === null) {
			$em->persist($eiEntityObj->getEntityObj());
			$em->flush();
			$eiEntityObj->refreshId();
			$eiEntityObj->setPersistent(true);
			return true;
		}
		
		$nsu = $this->createNestedSetUtils($nestedSetStrategy);
		$nsu->insert($eiEntityObj->getEntityObj());
		$eiEntityObj->refreshId();
		$eiEntityObj->setPersistent(true);
		return true;
	}
	
	/**
	 * @param NestedSetStrategy $nestedSetUtils
	 * @return \n2n\persistence\orm\util\NestedSetUtils
	 */
	private function createNestedSetUtils(NestedSetStrategy $nestedSetStrategy) {
		return new NestedSetUtils($this->eiuAnalyst->getEiLaunch(true)->getEntityManager(),
				$this->eiuAnalyst->getEiFrame(true)->getContextEiEngine()->getEiMask()->getEiType()->getEntityModel()->getClass(),
				$nestedSetStrategy);
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \n2n\persistence\orm\util\NestedSetUtils
	 */
	private function valNestedInsertable() {
		if (!$this->eiEntry->isNew()) {
			throw new IllegalStateException('EiEntry is not new.');
		}
		
		$nestedSetStrategy = $this->eiEntry->getEiType()->getNestedSetStrategy();
		if ($nestedSetStrategy === null) {
			throw new IllegalStateException($this->eiEntry->getEiType()->__toString() . ' has no NestedSetStrategy.');
		}
		
		return $this->createNestedSetUtils($nestedSetStrategy);
	}
	
	function insertAfter($eiObjectArg) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg);
		
		if (!$this->eiEntry->save(false)) {
			return false;
		}
		
		$nsu = $this->valNestedInsertable();
		$nsu->insertAfter($this->eiEntry->getEiObject()->getEiEntityObj()->getEntityObj(), $eiObject->getEiEntityObj()->getEntityObj());
		$this->eiuAnalyst->getEiLaunch(true)->getEntityManager()->flush();
		return true;
	}
	
	function insertBefore($eiObjectArg) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg);
		
		if (!$this->eiEntry->save(false)) {
			return false;
		}
		
		$nsu = $this->valNestedInsertable();
		$nsu->insertBefore($this->eiEntry->getEiObject()->getEiEntityObj()->getEntityObj(), $eiObject->getEiEntityObj()->getEntityObj());
		$this->eiuAnalyst->getEiLaunch(true)->getEntityManager()->flush();
		return true;
	}
	
	function insertAsChild($parentEiObjectArg) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($parentEiObjectArg);
		
		if (!$this->eiEntry->save(false)) {
			return false;
		}
		
		$nsu = $this->valNestedInsertable();
		$nsu->insert($this->eiEntry->getEiObject()->getEiEntityObj()->getEntityObj(), $eiObject->getEiEntityObj()->getEntityObj());
		$this->eiuAnalyst->getEiLaunch(true)->getEntityManager()->flush();
		return true;
	}
	
	function remove(): TransactionApproveAttempt {
		$ms = $this->eiuAnalyst->getManageState();
		$ms->remove($this->getEiObject());
		return $ms->flush();
	}
	
	/**
	 * @return \rocket\ui\si\content\SiEntryQualifier
	 */
//	function createSiEntryQualifier(): \rocket\ui\si\content\SiEntryQualifier {
//		$factory = new EiSiEntryQualifierFactory($this->eiuAnalyst->getN2nContext(true));
//		$siMaskQualifier = $factory->create($this->getEiEntry(true));
//		$idName = $this->createIdentityString();
//
//		if ($this->eiuObject !== null) {
//			return $this->eiuObject->getEiObject()->createSiEntryIdentifier()->toQualifier($siMaskQualifier, $idName);
//		}
//
//		return $this->eiEntry->getEiObject()->createSiEntryIdentifier()->toQualifier($siMaskQualifier, $idName);
//	}

	function createSiObjectQualifier(): SiObjectQualifier {
		$factory = new EiSiObjectQualifierFactory($this->eiuAnalyst->getN2nContext(true));
		return $factory->createFromEiEntry($this->getEiEntry(true));
	}
	
	function getMessages($eiPropPath = null, bool $recursive = false) {
		$eiPropPath = EiPropPath::build($eiPropPath);
		
		$eiEntry = $this->getEiEntry(false);
		if ($eiEntry === null || !$eiEntry->hasValidationResult()) {
			return [];
		}
		
		$validationResult = $eiEntry->getValidationResult();
		if ($eiPropPath === null) {
			return $validationResult->getMessages($recursive);
		}
		
		$fieldValidationResult = $validationResult->getEiFieldValidationResult($eiPropPath);
		if ($fieldValidationResult === null) {
			return [];
		}
		
		return $fieldValidationResult->getMessages($recursive);
	}
	
	function getMessagesAsStrs($eiPropPath = null, bool $recursive = false) {
		return array_map(fn ($m) => $m->t($this->eiuAnalyst->getN2nContext(true)->getN2nLocale()), 
				$this->getMessages($eiPropPath));
	}

	function createBulkyGui(bool $readOnly): BulkyGui {
		$factory = new EiGuiFactory($this->eiuAnalyst->getEiFrame(true));
		return $factory->createBulkyGui([$this->getEiEntry()], $readOnly);
	}

	function createCompactGui(bool $readOnly): CompactGui {
		$factory = new EiGuiFactory($this->eiuAnalyst->getEiFrame(true));
		return $factory->createCompactGui([$this->getEiEntry()], $readOnly);
	}
}  

// class InitListener implements EiGuiValueBoundaryListener {
// 	private $eiGuiValueBoundaryAssembler;
	
// 	public function __construct(EiGuiValueBoundaryAssembler $eiGuiValueBoundaryAssembler) {
// 		$this->eiGuiValueBoundaryAssembler = $eiGuiValueBoundaryAssembler;
// 	}
	
// 	public function finalized(EiGuiValueBoundary $eiGuiValueBoundary) {
// 		$eiGuiValueBoundary->unregisterEiGuiValueBoundaryListener($this);
		
// 		$this->eiGuiValueBoundaryAssembler->finalize();
// 	}

// 	public function onSave(EiGuiValueBoundary $eiGuiValueBoundary) {
// 	}

// 	public function saved(EiGuiValueBoundary $eiGuiValueBoundary) {
// 	}
// }

//class DummyEiGuiSiFactory implements EiGuiSiFactory {
//
//
//	public function getSiStructureDeclarations(): array {
//		throw new NotYetImplementedException();
//	}
//
//	public function getSiProps(): array {
//		throw new NotYetImplementedException();
//	}
//}
