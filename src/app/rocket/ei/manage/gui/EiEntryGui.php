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
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\si\input\SiEntryInput;
use n2n\util\type\attrs\AttributesException;

class EiEntryGui {
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
	public function __construct(EiEntry $eiEntry, int $treeLevel = null) {
		$this->eiEntry = $eiEntry;
		$this->treeLevel = $treeLevel;
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
	public function getGuiFieldMap() {
		$this->ensureInitialized();
		
		return $this->guiFieldMap;
	}
	
	/**
	 * @param GuiPropPath $prefixGuiPropPath
	 * @return \rocket\ei\manage\gui\field\GuiField[]
	 */
	public function filterGuiFields(GuiPropPath $prefixGuiPropPath, bool $checkOnEiPropPathLevel) {
		$this->ensureInitialized();
		
		$guiFields = [];
		
		foreach ($this->guiFields as $guiPropPathStr => $guiField) {
			$guiPropPath = GuiPropPath::create($guiPropPathStr);
			if ($guiPropPath->equals($prefixGuiPropPath) 
					|| !$guiPropPath->startsWith($prefixGuiPropPath, $checkOnEiPropPathLevel)) {
				continue;
			}
				
			$guiFields[] = $guiField;
		}
		
		return $guiFields;
	}
	
	
// 	/**
// 	 * @param GuiPropPath $guiPropPath
// 	 * @param GuiFieldFork $guiFieldForkAssembly
// 	 */
// 	public function putGuiFieldFork(GuiPropPath $guiPropPath, GuiFieldFork $guiFieldFork) {
// 		$this->ensureNotInitialized();
		
// 		$key = (string) $guiPropPath;
		
// 		if (isset($this->guiFieldForks[$key])) {
// 			throw new IllegalStateException('GuiPropPath already initialized: ' . $guiPropPath);
// 		}
		
// 		$this->guiFieldForks[$key] = $guiFieldFork;
// 	}
	
	
// 	/**
// 	 * @param GuiPropPath $guiPropPath
// 	 * @return bool
// 	 */
// 	public function containsGuiFieldForkGuiPropPath(GuiPropPath $guiPropPath) {
// 		return isset($this->guiFieldForks[(string) $guiPropPath]);
// 	}
	
// 	/**
// 	 * @return \rocket\ei\manage\gui\field\GuiPropPath[]
// 	 */
// 	public function getGuiFieldForkGuiPropPaths() {
// 		$eiPropPaths = array();
// 		foreach (array_keys($this->guiFieldForks) as $eiPropPathStr) {
// 			$eiPropPaths[] = GuiPropPath::create($eiPropPathStr);
// 		}
// 		return $eiPropPaths;
// 	}
	
	
// 	/**
// 	 * @param GuiPropPath $guiPropPath
// 	 * @throws GuiException
// 	 * @return GuiFieldFork
// 	 */
// 	public function getGuiFieldForkAssembly(GuiPropPath $guiPropPath) {
// 		$eiPropPathStr = (string) $guiPropPath;
// 		if (!isset($this->guiFieldForks[$eiPropPathStr])) {
// 			throw new GuiException('No GuiFieldFork with GuiPropPath \'' . $eiPropPathStr . '\' for \'' . $this . '\' registered');
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
		
		$this->getGuiFieldMap()->save();
		
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
	 * @param SiEntryInput $siEntryInput
	 * @throws IllegalStateException
	 * @throws \InvalidArgumentException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		if ($this->eiEntry->getEiType()->getId() != $siEntryInput->getTypeId()) {
			throw new IllegalStateException('EiType missmatch.');
		}
		
		
		foreach ($this->guiFieldMap->getAllGuiFields() as $guiPropPathStr => $guiField) {
			$siField = $guiField->getSiField();
			
			if ($siField == null || $siField->isReadOnly()
					|| !$siEntryInput->containsFieldName($guiPropPathStr)) {
				continue;
			}
			
			try {
				$siField->handleInput($siEntryInput->getFieldInput($guiPropPathStr)->getData());
			} catch (AttributesException $e) {
				throw new \InvalidArgumentException(null, 0, $e);
			}
		}
	}
	
	public function __toString() {
		return 'EiEntryGui of ' . $this->eiEntry;
	}
}