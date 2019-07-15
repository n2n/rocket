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

use n2n\web\dispatch\map\PropertyPath;
use n2n\util\ex\IllegalStateException;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\gui\field\GuiFieldFork;
use rocket\ei\manage\gui\field\GuiField;
use rocket\si\content\SiEntryBuildup;
use rocket\si\content\SiEntry;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\api\ApiControlCallId;

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
	 * @var GuiField[]
	 */
	private $guiFields = array();
	/**
	 * @var GuiFieldFork[]
	 */
	private $guiFieldForks = array();
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
	 * @param GuiFieldPath $guiFieldPath
	 * @return \n2n\web\dispatch\map\PropertyPath
	 */
	public function createPropertyPath(GuiFieldPath $guiFieldPath) {
		if ($this->contextPropertyPath !== null) {
			return $this->contextPropertyPath->ext((string) $guiFieldPath);
		}
		
		return new PropertyPath(array((string) $guiFieldPath));
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return bool
	 */
	public function containsGuiFieldPath(GuiFieldPath $guiFieldPath) {
		return isset($this->guiFields[(string) $guiFieldPath])
				|| isset($this->guiFieldForks[(string) $guiFieldPath]);
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @param GuiField $guiField
	 */
	public function putGuiField(GuiFieldPath $guiFieldPath, GuiField $guiField) {
		$this->ensureNotInitialized();
		
		$key = (string) $guiFieldPath;
		
		if (isset($this->guiFields[$key])) {
			throw new IllegalStateException('GuiFieldPath already initialized: ' . $guiFieldPath);
		}
		
		$this->guiFields[$key] = $guiField;
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return bool
	 */
	public function containsGuiFieldGuiFieldPath(GuiFieldPath $guiFieldPath) {
		return isset($this->guiFields[(string) $guiFieldPath]);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\field\GuiFieldPath[]
	 */
	public function getGuiFieldGuiFieldPaths() {
		$guiFieldPaths = array();
		foreach (array_keys($this->guiFields) as $eiPropPathStr) {
			$guiFieldPaths[] = GuiFieldPath::create($eiPropPathStr);
		}
		return $guiFieldPaths;
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @throws GuiException
	 * @return GuiField
	 */
	public function getGuiField(GuiFieldPath $guiFieldPath) {
		$guiFieldPathStr = (string) $guiFieldPath;
		if (!isset($this->guiFields[$guiFieldPathStr])) {
			throw new GuiException('No GuiField with GuiFieldPath \'' . $guiFieldPathStr . '\' for \'' . $this . '\' registered');
		}
		
		return $this->guiFields[$guiFieldPathStr];
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
	
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @param GuiFieldFork $guiFieldForkAssembly
	 */
	public function putGuiFieldFork(GuiFieldPath $guiFieldPath, GuiFieldFork $guiFieldFork) {
		$this->ensureNotInitialized();
		
		$key = (string) $guiFieldPath;
		
		if (isset($this->guiFieldForks[$key])) {
			throw new IllegalStateException('GuiFieldPath already initialized: ' . $guiFieldPath);
		}
		
		$this->guiFieldForks[$key] = $guiFieldFork;
	}
	
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return bool
	 */
	public function containsGuiFieldForkGuiFieldPath(GuiFieldPath $guiFieldPath) {
		return isset($this->guiFieldForks[(string) $guiFieldPath]);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\field\GuiFieldPath[]
	 */
	public function getGuiFieldForkGuiFieldPaths() {
		$eiPropPaths = array();
		foreach (array_keys($this->guiFieldForks) as $eiPropPathStr) {
			$eiPropPaths[] = GuiFieldPath::create($eiPropPathStr);
		}
		return $eiPropPaths;
	}
	
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @throws GuiException
	 * @return GuiFieldFork
	 */
	public function getGuiFieldForkAssembly(GuiFieldPath $guiFieldPath) {
		$eiPropPathStr = (string) $guiFieldPath;
		if (!isset($this->guiFieldForks[$eiPropPathStr])) {
			throw new GuiException('No GuiFieldFork with GuiFieldPath \'' . $eiPropPathStr . '\' for \'' . $this . '\' registered');
		}
		
		return $this->guiFieldForks[$eiPropPathStr];
	}
	
	/**
	 * @return GuiFieldFork[]
	 */
	public function getGuiFieldForks() {
		return $this->guiFieldForks;
	}

	public function save() {
		$this->ensureInitialized();
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->onSave($this);
		}
		
		foreach ($this->guiFields as $guiField) {
			if (!$guiField->getSiField()->isReadOnly()) {
				$guiField->save();
			}
		}
		
		foreach ($this->guiFieldForks as $guiFieldForkAssembly) {
			if (null !== ($guiField = $guiFieldForkAssembly->getEditable())) {
				$savable->save();
			}
		}
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->saved($this);
		}
	}
	
	
	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->initialized;
	}
	
	private function ensureInitialized() {
		if ($this->initialized) return;
		
		throw new IllegalStateException('EiEntryGui not yet initlized.');
	}
	
	private function ensureNotInitialized() {
		if (!$this->initialized) return;
		
		throw new IllegalStateException('EiEntryGui already initialized.');
	}
	
	public function markInitialized() {
		$this->ensureNotInitialized();
		
		$this->initialized = true;
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->finalized($this);
		}
	}
	
	public function registerEiEntryGuiListener(EiEntryGuiListener $eiEntryGuiListener) {
		$this->eiEntryGuiListeners[spl_object_hash($eiEntryGuiListener)] = $eiEntryGuiListener;
	}
	
	public function unregisterEiEntryGuiListener(EiEntryGuiListener $eiEntryGuiListener) {
		unset($this->eiEntryGuiListeners[spl_object_hash($eiEntryGuiListener)]);
	}
	
	/**
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry() {
		$eiType = $this->eiEntry->getEiType();
		
		$n2nContext = $this->eiGui->getEiFrame()->getN2nContext();
		$idNameDefinition = $this->eiGui->getEiFrame()->getManageState()->getDef()
				->getIdNameDefinition($this->eiEntry->getEiMask());
		$name = $idNameDefinition->createIdentityString($this->eiEntry->getEiObject(), $n2nContext, $n2nContext->getN2nLocale());
		
		$siQualifier = $this->eiEntry->getEiObject()->createSiQualifier($name);
		$siEntry = new SiEntry($siQualifier, !ViewMode::isReadOnly($this->eiGui->getViewMode()));
		$siEntry->putBuildup($eiType->getId(), $this->createSiEntryBuildup());
		return $siEntry;
	}
	
	/**
	 * @return SiEntryBuildup
	 */
	function createSiEntryBuildup() {
		$eiEntry = $this->eiEntry;
		$eiFrame = $this->eiGui->getEiFrame();
		
		$name = null;
		$deterIdNameDefinition = null;
		if ($eiEntry->isNew()) {
			$deterIdNameDefinition = $this->eiGui->getGuiDefinition();
			$name = $eiEntry->getEiMask()->getLabelLstr()->t($eiFrame->getN2nContext()->getN2nLocale());
		} else {
			$deterIdNameDefinition = $eiFrame->getManageState()->getDef()
					->getIdNameDefinition($eiEntry->getEiMask());
			$name = $deterIdNameDefinition->createIdentityString($eiEntry->getEiObject(), $eiFrame->getN2nContext(),
					$eiFrame->getN2nContext()->getN2nLocale());
		}
		
		$siEntry = new SiEntryBuildup($name);
		
		foreach ($this->guiFields as $guiFieldPathStr => $guiField) {
			$siEntry->putField($guiFieldPathStr, $guiField->getSiField());
		}
		
		foreach ($this->eiGui->getGuiDefinition()->createEntryGuiControls($this->eiGui, $eiEntry)
				as $guiControlPathStr => $entryGuiControl) {
			$siEntry->putControl($guiControlPathStr, $entryGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr), 
							$this->eiGui->getEiMask()->getEiTypePath(), $this->eiGui->getViewMode(), 
							$eiEntry->getPid())));
		}
		
		return $siEntry;
	}
	
	public function __toString() {
		return 'EiEntryGui of ' . $this->eiEntry;
	}
}