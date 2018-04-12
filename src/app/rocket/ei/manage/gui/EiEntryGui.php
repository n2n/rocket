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

use n2n\reflection\ArgUtils;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\Dispatchable;
use n2n\web\dispatch\mag\MagWrapper;
use n2n\util\ex\IllegalStateException;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\mapping\EiEntry;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\util\model\Eiu;
use rocket\ei\manage\control\EntryControlComponent;
use rocket\ei\EiCommandPath;
use rocket\ei\mask\model\ControlOrder;
use rocket\ei\manage\control\Control;

class EiEntryGui {
	private $eiGui;
	private $eiEntry;
	private $treeLevel;
	private $guiFieldAssemblies = array();
	private $guiFieldForkAssemblies = array();
	private $eiEntryGuiListeners = array();
	private $initialized = false;
	
	private $dispatchable;
	private $contextPropertyPath = null;
	private $forkMagPropertyPaths = array();
	private $savables = array();
	
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
	 * @return \rocket\ei\manage\mapping\EiEntry
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
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->initialized;
	}
		
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return \n2n\web\dispatch\map\PropertyPath
	 */
	public function createPropertyPath(GuiIdPath $guiIdPath) {
		if ($this->contextPropertyPath !== null) {
			return $this->contextPropertyPath->ext((string) $guiIdPath);
		}
		
		return new PropertyPath(array((string) $guiIdPath));
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return bool
	 */
	public function containsGuiIdPath(GuiIdPath $guiIdPath) {
		return isset($this->guiFieldAssemblies[(string) $guiIdPath])
				|| isset($this->guiFieldForkAssemblies[(string) $guiIdPath]);
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @param Displayable $displayable
	 */
	public function putGuiFieldAssembly(GuiIdPath $guiIdPath, GuiFieldAssembly $guiFieldAssembly) {
		$this->ensureNotInitialized();
		
		$key = (string) $guiIdPath;
		
		if (isset($this->guiFieldAssemblies[$key])) {
			throw new IllegalStateException('GuiIdPath already initialized: ' . $guiIdPath);
		}
		
		$this->guiFieldAssemblies[$key] = $guiFieldAssembly;
	}
	
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return bool
	 */
	public function containsGuiFieldGuiIdPath(GuiIdPath $guiIdPath) {
		return isset($this->guiFieldAssemblies[(string) $guiIdPath]);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\GuiIdPath[]
	 */
	public function getGuiFieldGuiIdPaths() {
		$guiIdPaths = array();
		foreach (array_keys($this->guiFieldAssemblies) as $guiIdPathStr) {
			$guiIdPaths[] = GuiIdPath::create($guiIdPathStr);
		}
		return $guiIdPaths;
	}
	
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @throws GuiException
	 * @return GuiFieldAssembly
	 */
	public function getGuiFieldAssembly(GuiIdPath $guiIdPath) {
		$guiIdPathStr = (string) $guiIdPath;
		if (!isset($this->guiFieldAssemblies[$guiIdPathStr])) {
			throw new GuiException('No GuiField with GuiIdPath \'' . $guiIdPathStr . '\' for \'' . $this . '\' registered');
		}
		
		return $this->guiFieldAssemblies[$guiIdPathStr];
	}
	
	public function getGuiFieldAssemblies() {
		return $this->guiFieldAssemblies;
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @param Displayable $displayable
	 */
	public function putGuiFieldForkAssembly(GuiIdPath $guiIdPath, GuiFieldForkAssembly $guiFieldForkAssembly) {
		$this->ensureNotInitialized();
		
		$key = (string) $guiIdPath;
		
		if (isset($this->guiFieldForkAssemblies[$key])) {
			throw new IllegalStateException('GuiIdPath already initialized: ' . $guiIdPath);
		}
		
		$this->guiFieldForkAssemblies[$key] = $guiFieldForkAssembly;
	}
	
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return bool
	 */
	public function containsGuiFieldForkGuiIdPath(GuiIdPath $guiIdPath) {
		return isset($this->guiFieldForkAssemblies[(string) $guiIdPath]);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\GuiIdPath[]
	 */
	public function getGuiFieldForkGuiIdPaths() {
		$guiIdPaths = array();
		foreach (array_keys($this->guiFieldForkAssemblies) as $guiIdPathStr) {
			$guiIdPaths[] = GuiIdPath::create($guiIdPathStr);
		}
		return $guiIdPaths;
	}
	
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @throws GuiException
	 * @return GuiFieldForkAssembly
	 */
	public function getGuiFieldForkAssembly(GuiIdPath $guiIdPath) {
		$guiIdPathStr = (string) $guiIdPath;
		if (!isset($this->guiFieldForkAssemblies[$guiIdPathStr])) {
			throw new GuiException('No GuiFieldFork with GuiIdPath \'' . $guiIdPathStr . '\' for \'' . $this . '\' registered');
		}
		
		return $this->guiFieldForkAssemblies[$guiIdPathStr];
	}
	
	/**
	 * @return GuiFieldForkAssembly[]
	 */
	public function getGuiFieldForkAssemblies() {
		return $this->guiFieldForkAssemblies;
	}
	
	/**
	 * @return MagAssembly[]
	 */
	public function getForkMagAssemblies() {
		$forkMagAssemblies = array();
		foreach ($this->guiFieldForkAssemblies as $guiFieldForkAssembly) {
			$magAssemblies = $guiFieldForkAssembly->getMagAssemblies();
			
			if (empty($magAssemblies)) continue;
			
			array_push($forkMagAssemblies, ...$magAssemblies);
		}
		return $forkMagAssemblies;
	}
	
// 	public function getEiPropPathByGuiIdPath(GuiIdPath $guiIdPath) {
// 		$guiIdPathStr = (string) $guiIdPath;
		
// 		if (isset($this->eiPropPaths[$guiIdPathStr])) {
// 			return $this->eiPropPaths[$guiIdPathStr];
// 		}
		
// 		return null;
// 	}

	/**
	 * @return \n2n\web\dispatch\Dispatchable|null
	 */
	public function getDispatchable() {
		return $this->dispatchable;
	}
	
	/**
	 * @param Dispatchable $dispatchable
	 */
	public function setDispatchable(?Dispatchable $dispatchable) {
		$this->ensureNotInitialized();
		$this->dispatchable = $dispatchable;
	}
		
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath|null
	 */
	public function getContextPropertyPath() {
		if ($this->contextPropertyPath !== null) {
			return $this->contextPropertyPath;
		}
		
		if ($this->dispatchable !== null) {
			return new PropertyPath(array());
		}
		
		return null;
	}

	/**
	 * @param PropertyPath|null $contextPropertyPath
	 */
	public function setContextPropertyPath(?PropertyPath $contextPropertyPath) {
		$this->contextPropertyPath = $contextPropertyPath;
	}
	
	public function save() {
		$this->ensureInitialized();
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->onSave($this);
		}
		
		foreach ($this->guiFieldAssemblies as $guiFieldAssembly) {
			if (null !== ($savable = $guiFieldAssembly->getSavable())) {
				$savable->save();
			}
		}
		
		foreach ($this->guiFieldForkAssemblies as $guiFieldForkAssembly) {
			if (null !== ($savable = $guiFieldForkAssembly->getSavable())) {
				$savable->save();
			}
		}
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->saved($this);
		}
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
	
	public function createControls(HtmlView $view) {
		$eiFrame = $this->eiGui->getEiFrame();
		$eiMask = $eiFrame->determineEiMask($this->eiEntry->getEiType());
	
		$eiu = new Eiu($this);
	
		$controls = array();
	
		foreach ($eiMask->getEiCommandCollection() as $eiCommandId => $eiCommand) {
			if (!($eiCommand instanceof EntryControlComponent)
					|| !$this->eiEntry->isExecutableBy(EiCommandPath::from($eiCommand))) {
				continue;
			}

			$entryControls = $eiCommand->createEntryControls($eiu, $view);
			ArgUtils::valArrayReturn($entryControls, $eiCommand, 'createEntryControls', Control::class);
			foreach ($entryControls as $controlId => $control) {
				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
			}
		}
	
		$controls = $eiMask->sortEntryControls($controls, $this, $view);
		ArgUtils::valArrayReturn($controls, $eiMask, 'sortControls', Control::class);
			
		return $controls;
	}
	
	public function __toString() {
		return 'EiEntryGui of ' . $this->eiEntry;
	}
}


class MagAssembly {
	private $mandatory;
	private $magPropertyPath;
	private $magWrapper;
	
	public function __construct(bool $mandatory, PropertyPath $magPropertyPath, MagWrapper $magWrapper) {
		$this->mandatory = $mandatory;
		$this->magPropertyPath = $magPropertyPath;
		$this->magWrapper = $magWrapper;
	}
	
	/**
	 * @return boolean
	 */
	public function isMandatory() {
		return $this->mandatory;
	}
	
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath
	 */
	public function getMagPropertyPath() {
		return $this->magPropertyPath;
	}
	
	/**
	 * @return \n2n\web\dispatch\mag\Mag
	 */
	public function getMagWrapper() {
		return $this->magWrapper;
	}
}


// 	/**
// 	 * @param GuiIdPath $guiIdPath
// 	 * @throws GuiException
// 	 * @return EiFieldWrapper
// 	 */
// 	public function getEiFieldWrapperByGuiIdPath(GuiIdPath $guiIdPath) {
// 		$guiIdPathStr = (string) $guiIdPath;

// 		if (!isset($this->eiFieldWrappers[$guiIdPathStr])) {
// 			throw new GuiException('No EiFieldWrapper with GuiIdPath ' . $guiIdPathStr . ' registered');
// 		}

// 		return $this->eiFieldWrappers[$guiIdPathStr];
// 	}


// 	/**
// 	 * @param GuiIdPath $guiIdPath
// 	 * @return bool
// 	 */
// 	public function containsEiFieldWrapper(GuiIdPath $guiIdPath) {
// 		return isset($this->eiFieldWrappers[(string) $guiIdPath]);
// 	}