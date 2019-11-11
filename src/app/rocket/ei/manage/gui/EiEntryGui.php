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
namespace rocket\ei\manage\gui;

use n2n\util\ex\IllegalStateException;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\si\content\SiEntryBuildup;
use rocket\si\content\SiEntry;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\api\ApiControlCallId;
use rocket\si\input\SiEntryInput;
use rocket\si\content\impl\basic\BulkyEntrySiComp;
use rocket\si\content\impl\basic\CompactEntrySiComp;
use n2n\util\type\attrs\AttributesException;

class EiEntryGui {
	/**
	 * @var EiGui
	 */
	private $eiGui;
	/**
	 * @var EiEntry
	 */
	private $eiEntry;
	/**
	 * @var int|null
	 */
	private $treeLevel;
	/**
	 * @var GuiFieldMap
	 */
	private $guiFieldMap;
	/**
	 * @var EiEntryGuiListener[]
	 */
	private $eiEntryGuiListeners = array();
	/**
	 * @var bool
	 */
	private $initialized = false;
	
	/**
	 * @param EiMask $eiMask
	 * @param int $viewMode
	 * @param int|null $treeLevel
	 */
	public function __construct(EiGui $eiGui, EiEntry $eiEntry, int $treeLevel = null) {
		$this->eiGui = $eiGui;
		$this->eiEntry = $eiEntry;
		$this->treeLevel = $treeLevel;
	}

	/**
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	public function getEiGui() {
		return $this->eiGui;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiEntry
	 */
	public function getEiEntry() {
		return $this->eiEntry;
	}
	
	/**
	 * @return int|null
	 */
	public function getTreeLevel() {
		return $this->treeLevel;
	}
	
	
	
	/**
	 * @return \rocket\ei\manage\gui\field\GuiField[]
	 */
	public function getGuiFields() {
		$this->ensureInitialized();
		
		return $this->guiFields;
	}
	
	/**
	 * @param GuiFieldPath $prefixGuiFieldPath
	 * @return \rocket\ei\manage\gui\field\GuiField[]
	 */
	public function filterGuiFields(GuiFieldPath $prefixGuiFieldPath, bool $checkOnEiPropPathLevel) {
		$this->ensureInitialized();
		
		$guiFields = [];
		
		foreach ($this->guiFields as $guiFieldPathStr => $guiField) {
			$guiFieldPath = GuiFieldPath::create($guiFieldPathStr);
			if ($guiFieldPath->equals($prefixGuiFieldPath) 
					|| !$guiFieldPath->startsWith($prefixGuiFieldPath, $checkOnEiPropPathLevel)) {
				continue;
			}
				
			$guiFields[] = $guiField;
		}
		
		return $guiFields;
	}
	
	
// 	/**
// 	 * @param GuiFieldPath $guiFieldPath
// 	 * @param GuiFieldFork $guiFieldForkAssembly
// 	 */
// 	public function putGuiFieldFork(GuiFieldPath $guiFieldPath, GuiFieldFork $guiFieldFork) {
// 		$this->ensureNotInitialized();
		
// 		$key = (string) $guiFieldPath;
		
// 		if (isset($this->guiFieldForks[$key])) {
// 			throw new IllegalStateException('GuiFieldPath already initialized: ' . $guiFieldPath);
// 		}
		
// 		$this->guiFieldForks[$key] = $guiFieldFork;
// 	}
	
	
// 	/**
// 	 * @param GuiFieldPath $guiFieldPath
// 	 * @return bool
// 	 */
// 	public function containsGuiFieldForkGuiFieldPath(GuiFieldPath $guiFieldPath) {
// 		return isset($this->guiFieldForks[(string) $guiFieldPath]);
// 	}
	
// 	/**
// 	 * @return \rocket\ei\manage\gui\field\GuiFieldPath[]
// 	 */
// 	public function getGuiFieldForkGuiFieldPaths() {
// 		$eiPropPaths = array();
// 		foreach (array_keys($this->guiFieldForks) as $eiPropPathStr) {
// 			$eiPropPaths[] = GuiFieldPath::create($eiPropPathStr);
// 		}
// 		return $eiPropPaths;
// 	}
	
	
// 	/**
// 	 * @param GuiFieldPath $guiFieldPath
// 	 * @throws GuiException
// 	 * @return GuiFieldFork
// 	 */
// 	public function getGuiFieldForkAssembly(GuiFieldPath $guiFieldPath) {
// 		$eiPropPathStr = (string) $guiFieldPath;
// 		if (!isset($this->guiFieldForks[$eiPropPathStr])) {
// 			throw new GuiException('No GuiFieldFork with GuiFieldPath \'' . $eiPropPathStr . '\' for \'' . $this . '\' registered');
// 		}
		
// 		return $this->guiFieldForks[$eiPropPathStr];
// 	}
	
	function init(GuiFieldMap $guiFieldMap) {
		$this->ensureNotInitialized();
		
		$this->guiFieldMap = $guiFieldMap;
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->finalized($this);
		}
	}
	
// 	/**
// 	 * @return GuiFieldFork[]
// 	 */
// 	public function getGuiFieldForks() {
// 		return $this->guiFieldForks;
// 	}

	public function save() {
		$this->ensureInitialized();
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->onSave($this);
		}
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->saved($this);
		}
	}
	
	
	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->guiFieldMap !== null;
	}
	
	private function ensureInitialized() {
		if ($this->isInitialized()) return;
		
		throw new IllegalStateException('EiEntryGui not yet initlized.');
	}
	
	private function ensureNotInitialized() {
		if (!$this->isInitialized()) return;
		
		throw new IllegalStateException('EiEntryGui already initialized.');
	}
	
	
	public function registerEiEntryGuiListener(EiEntryGuiListener $eiEntryGuiListener) {
		$this->eiEntryGuiListeners[spl_object_hash($eiEntryGuiListener)] = $eiEntryGuiListener;
	}
	
	public function unregisterEiEntryGuiListener(EiEntryGuiListener $eiEntryGuiListener) {
		unset($this->eiEntryGuiListeners[spl_object_hash($eiEntryGuiListener)]);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiEntryGuiMulti
	 */
	function toMulti() {
		return new EiEntryGuiMulti($this->eiEntry->getEiType(), [$this]);
	}
	
	/**
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry(bool $siControlsIncluded = true) {
		$eiType = $this->eiEntry->getEiType();
		$siIdentifier = $this->eiEntry->getEiObject()->createSiEntryIdentifier();
		$viewMode = $this->eiGui->getViewMode();

		$siEntry = new SiEntry($siIdentifier, ViewMode::isReadOnly($viewMode), ViewMode::isBulky($viewMode));
		$siEntry->putBuildup($eiType->getId(), $this->createSiEntryBuildup($siControlsIncluded));
		$siEntry->setSelectedTypeId($eiType->getId());

		return $siEntry;
	}
	
	/**
	 * @return SiEntryBuildup
	 */
	function createSiEntryBuildup(bool $siControlsIncluded = true) {
		$eiEntry = $this->eiEntry;
		$eiFrame = $this->eiGui->getEiFrame();
		
		$n2nLocale = $eiFrame->getN2nContext()->getN2nLocale();
		$typeId = $eiEntry->getEiMask()->getEiType()->getId();
		$idName = null;
		if (!$eiEntry->isNew()) {
			$deterIdNameDefinition = $eiFrame->getManageState()->getDef()
					->getIdNameDefinition($eiEntry->getEiMask());
			$idName = $deterIdNameDefinition->createIdentityString($eiEntry->getEiObject(), $eiFrame->getN2nContext(),
					$n2nLocale);
		}
		
		$siEntry = new SiEntryBuildup($typeId, $idName);
		
		foreach ($this->guiFieldMap->getGuiFields() as $guiFieldPathStr => $guiField) {
			$siEntry->putField($guiFieldPathStr, $guiField->getSiField());
		}
		
		if (!$siControlsIncluded) {
			return $siEntry;
		}
		
		foreach ($this->eiGui->getGuiDefinition()->createEntryGuiControls($this->eiGui, $eiEntry)
				as $guiControlPathStr => $entryGuiControl) {
			$siEntry->putControl($guiControlPathStr, $entryGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr), 
							$this->eiGui->getGuiDefinition()->getEiMask()->getEiTypePath(), 
							$this->eiGui->getViewMode(), $eiEntry->getPid())));
		}
		
		return $siEntry;
	}
		
	/**
	 * @param bool $controlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createCompactEntrySiComp(bool $generalSiControlsIncluded = true,
			bool $entrySiControlsIncluded = true) {
		$siContent = new CompactEntrySiComp($this->eiGui->createSiDeclaration(),
				$this->createSiEntry($entrySiControlsIncluded));
		
		if ($generalSiControlsIncluded) {
			$siContent->setControls($this->eiGui->createGeneralSiControls());
		}
		
		return $siContent;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createBulkyEntrySiComp(bool $generalSiControlsIncluded = true,
			bool $entrySiControlsIncluded = true) {
		$siContent = new BulkyEntrySiComp($this->eiGui->createSiDeclaration(),
				$this->createSiEntry($entrySiControlsIncluded));
		
		if ($generalSiControlsIncluded) {
			$siContent->setControls($this->eiGui->createGeneralSiControls());
		}
		
		return $siContent;
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 * @throws IllegalStateException
	 * @throws \InvalidArgumentException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		if ($this->eiEntry->getEiType()->getId() != $siEntryInput->getTypeId()) {
			throw new IllegalStateException('EiType missmatch.');
		}
		
		foreach ($this->getGuiFields() as $guiFieldPathStr => $guiField) {
			if ($guiField->getSiField()->isReadOnly()
					|| !$siEntryInput->containsFieldName($guiFieldPathStr)) {
				continue;
			}
			
			try {
				$guiField->getSiField()->handleInput($siEntryInput->getFieldInput($guiFieldPathStr)->getData());
			} catch (AttributesException $e) {
				throw new \InvalidArgumentException(null, 0, $e);
			}
		}
	}
	
	public function __toString() {
		return 'EiEntryGui of ' . $this->eiEntry;
	}
}