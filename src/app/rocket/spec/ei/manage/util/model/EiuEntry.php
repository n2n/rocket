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
use rocket\spec\ei\manage\EiEntry;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\util\ex\IllegalStateException;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\mapping\OnWriteMappingListener;
use rocket\spec\ei\manage\mapping\WrittenMappingListener;
use rocket\spec\ei\manage\mapping\OnValidateMappingListener;
use rocket\spec\ei\manage\mapping\ValidatedMappingListener;
use rocket\spec\ei\manage\mapping\MappingOperationFailedException;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\GuiException;

class EiuEntry {
	private $eiEntry;
	private $eiMapping;
	private $eiuFrame;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		$this->eiEntry = $eiuFactory->getEiEntry(true);
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
	
	public function gui($eiEntryGuiObj) {
		return new EiuEntryGui($eiEntryGuiObj, $this);
	}
	
	/**
	 * @param bool $eiEntryObj
	 * @param bool $editable
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuEntryGui
	 */
	public function newGui(bool $overview = false, bool $editable = false) {
		$eiEntryGui = null;
		if (!$overview) {
			$eiEntryGui = $this->getEiuFrame()->getEiMask()->createBulkyEiEntryGui($this, $editable);
		} else if (null === $this->getEiuFrame()->getNestedSetStrategy()) {
			$eiEntryGui = $this->getEiuFrame()->getEiMask()->createListEiEntryGui($this, $editable);
		} else {
			$eiEntryGui = $this->getEiuFrame()->getEiMask()->createTreeEiEntryGui($this, $editable);
		}
		
		return new EiuEntryGui($eiEntryGui, $this);
	}
	
	public function field($eiFieldObj) {
		return new EiuField($eiFieldObj, $this);
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
			return $this->eiMapping = $this->getEiuFrame()->createEiMapping($this->eiEntry);
		}
		
		return null;
	}
	
	public function getValue($eiFieldPath) {
		return $this->getEiMapping()->getValue($eiFieldPath);
	}
	
	public function setValue($eiFieldPath, $value) {
		return $this->getEiMapping()->setValue($eiFieldPath, $value);
	}
	
	public function getValues() {
		$eiMapping = $this->getEiMapping();
		$values = array();
		foreach (array_keys($eiMapping->getMappableWrappers()) as $eiFieldPathStr) {
			$values[$eiFieldPathStr] = $this->getEiMapping()->getValue($eiFieldPathStr);
		}
		return $values;
	}
	
	public function setScalarValue($eiFieldPath, $scalarValue) {
		$eiFieldPath = EiFieldPath::create($eiFieldPath);
		$scalarEiProperty = $this->getEiuFrame()->getEiMask()->getEiEngine()->getScalarEiDefinition()
				->getScalarEiPropertyByFieldPath($eiFieldPath);
		$this->setValue($eiFieldPath, $scalarEiProperty->scalarValueToMappableValue($scalarValue));
	}
	
	public function getScalarValue($eiFieldPath) {
		$eiFieldPath = EiFieldPath::create($eiFieldPath);
		$scalarEiProperty = $this->getEiuFrame()->getEiMask()->getEiEngine()->getScalarEiDefinition()
				->getScalarEiPropertyByFieldPath($eiFieldPath);
		return $scalarEiProperty->mappableValueToScalarValue($this->getValue($eiFieldPath));
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiEntry
	 */
	public function getEiEntry() {
		return $this->eiEntry;
	}
	
	public function isNew() {
		if ($this->isDraft()) {
			return $this->isDraftNew();
		} else {
			return !$this->isLivePersistent();
		}
	}
	
	/**
	 * @return \rocket\spec\ei\manage\LiveEntry
	 */
	public function getLiveEntry() {
		return $this->eiEntry->getLiveEntry();
	}
	
	/**
	 * @return boolean
	 */
	public function isLivePersistent() {
		return $this->eiEntry->getLiveEntry()->isPersistent();
	}
	
	public function hasLiveId() {
		return $this->eiEntry->getLiveEntry()->hasId();
	}
	
	/**
	 * @param bool $required
	 * @return mixed
	 */
	public function getLiveId(bool $required = true) {
		$liveEntry = $this->getLiveEntry();
		
		if (!$required && !$liveEntry->isPersistent()) {
			return null;
		}
		
		return $liveEntry->getId();
	}

	/*
	 * @param bool $required
	 * @return string
	 */
	public function getLiveIdRep(bool $required = true) {
		return $this->getEiSpec()->idToIdRep($this->getLiveId($required));
	}
	
	/**
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function getEiSpec() {
		return $this->getLiveEntry()->getEiSpec();
	}
	
	/**
	 * @return boolean
	 */
	public function isDraft() {
		return $this->eiEntry->isDraft();
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\spec\ei\manage\draft\Draft
	 */
	public function getDraft(bool $required = true) {
		if (!$required && !$this->isDraft()) {
			return null;
		}
		
		return $this->eiEntry->getDraft();
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
		return $this->eiuFrame->createIdentityString($this->eiEntry, $determineEiMask, $n2nLocale);
	}
	
	/**
	 * @param int $limit
	 * @param int $num
	 * @return \rocket\spec\ei\manage\draft\Draft[]
	 */
	public function lookupDrafts(int $limit = null, int $num = null) {
		return $this->eiuFrame->lookupDraftsByEntityObjId($this->getLiveId(), $limit, $num);
	}
	
	public function acceptsValue($eiFieldPath, $value) {
		return $this->getEiMapping()->acceptsValue(EiFieldPath::create($eiFieldPath), $value);
	}
	
	/**
	 * 
	 * @param unknown $eiFieldPath
	 * @param bool $required
	 * @throws MappingOperationFailedException
	 * @return \rocket\spec\ei\manage\mapping\MappableWrapper|null
	 */
	public function getMappableWrapper($eiFieldPath, bool $required = false) {
		try {
			return $this->getEiMapping()->getMappableWrapper(EiFieldPath::create($eiFieldPath));
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
		return !empty($this->eiuFrame->getPreviewTypeOptions($this->eiEntry));
	}
	
	public function getPreviewType() {
		return $this->getEiuFrame()->getPreviewType($this->eiEntry);
	}
	
	public function getPreviewTypeOptions() {
		return $this->eiuFrame->getPreviewTypeOptions($this->eiEntry);
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
}  