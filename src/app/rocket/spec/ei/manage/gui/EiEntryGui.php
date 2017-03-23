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
namespace rocket\spec\ei\manage\gui;

use n2n\reflection\ArgUtils;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\Dispatchable;
use n2n\web\dispatch\mag\Mag;
use n2n\web\dispatch\mag\MagWrapper;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\mapping\MappableWrapper;

class EiEntryGui {
	private $guiDefinition;
	private $viewMode;
	private $displayables = array();
	private $mappableWrappers = array();
// 	private $eiFieldPaths = array();
	private $eiEntryGuiListeners = array();
	private $initialized = false;
	
	private $dispatchable;
	private $contextPropertyPath = null;
	private $forkMagPropertyPaths = array();
	private $savables = array();
	
	public function __construct(GuiDefinition $guiDefinition, int $viewMode) {
		$this->guiDefinition = $guiDefinition;
		$this->viewMode = $viewMode;
	}
	
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	public function getViewMode() {
		return $this->viewMode;
	}
	
	public function isInitialized() {
		return $this->initialized;
	}
		
	public function createPropertyPath(GuiIdPath $guiIdPath) {
		if ($this->contextPropertyPath !== null) {
			return $this->contextPropertyPath->ext((string) $guiIdPath);
		}
		
		return new PropertyPath(array((string) $guiIdPath));
	}
	
	public function putDisplayable(GuiIdPath $guiIdPath, Displayable $displayable) {
		$this->displayables[(string) $guiIdPath] = $displayable;
	}
	
// 	public function putEiFieldPath(GuiIdPath $guiIdPath, EiFieldPath $eiFieldPath) {
// 		$this->eiFieldPaths[(string) $guiIdPath] = $eiFieldPath;
// 	}
	
	public function containsDisplayable(GuiIdPath $guiIdPath) {
		return isset($this->displayables[(string) $guiIdPath]);
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @throws GuiException
	 * @return MappableWrapper
	 */
	public function getMappableWrapperByGuiIdPath(GuiIdPath $guiIdPath) {
		$guiIdPathStr = (string) $guiIdPath;
	
		if (!isset($this->mappableWrappers[$guiIdPathStr])) {
			throw new GuiException('No MappableWrapper with GuiIdPath ' . $guiIdPathStr . ' registered');
		}
	
		return $this->mappableWrappers[$guiIdPathStr];
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @param MappableWrapper $mappableWrapper
	 */
	public function putMappableWrapper(GuiIdPath $guiIdPath, MappableWrapper $mappableWrapper) {
		$this->mappableWrappers[(string) $guiIdPath] = $mappableWrapper;
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return bool
	 */
	public function containsMappableWrapper(GuiIdPath $guiIdPath) {
		return isset($this->mappableWrappers[(string) $guiIdPath]);
	}
	
	public function getDisplayableByGuiIdPath(GuiIdPath $guiIdPath) {
		$guiIdPathStr = (string) $guiIdPath;
		
		if (!isset($this->displayables[$guiIdPathStr])) {
			throw new GuiException('No GuiElement with GuiIdPath ' . $guiIdPathStr . ' registered');
		}
	
		return $this->displayables[$guiIdPathStr];
	}
	
	public function getDisplayables() {
		return $this->displayables;
	}
	
// 	public function getEiFieldPathByGuiIdPath(GuiIdPath $guiIdPath) {
// 		$guiIdPathStr = (string) $guiIdPath;
		
// 		if (isset($this->eiFieldPaths[$guiIdPathStr])) {
// 			return $this->eiFieldPaths[$guiIdPathStr];
// 		}
		
// 		return null;
// 	}

	/**
	 * @return \n2n\web\dispatch\Dispatchable
	 */
	public function getDispatchable() {
		return $this->dispatchable;
	}
	
	/**
	 * @param Dispatchable $dispatchable
	 */
	public function setDispatchable(Dispatchable $dispatchable = null) {
		$this->dispatchable = $dispatchable;
	}
		
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath|null
	 */
	public function getContextPropertyPath() {
		return $this->contextPropertyPath;
	}

	/**
	 * @param PropertyPath $contextPropertyPath
	 */
	public function setContextPropertyPath(PropertyPath $contextPropertyPath = null) {
		$this->contextPropertyPath = $contextPropertyPath;
	}
	
	public function putEditableWrapper(GuiIdPath $guiIdPath, EditableWrapper $editableInfo) {
		$this->editableInfos[(string) $guiIdPath] = $editableInfo;
	}
	
	public function containsEditableWrapperGuiIdPath(GuiIdPath $guiIdPath): bool {
		return isset($this->editableInfos[(string) $guiIdPath]);
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @throws GuiException
	 * @return EditableWrapper
	 */
	public function getEditableWrapperByGuiIdPath(GuiIdPath $guiIdPath) {
		$guiIdPathStr = (string) $guiIdPath;
		
		if (!isset($this->editableInfos[$guiIdPathStr])) {
			throw new GuiException('No Mag with GuiIdPath ' . $guiIdPathStr . ' registered');
		}
		
		return $this->editableInfos[$guiIdPathStr];
	}
	
	public function getForkMagPropertyPaths(): array {
		return $this->forkMagPropertyPaths;
	}
	
	public function setForkMagPropertyPaths(array $forkMagPropertyPaths) {
		ArgUtils::valArray($forkMagPropertyPaths, 'n2n\web\dispatch\map\PropertyPath');
		$this->forkMagPropertyPaths = $forkMagPropertyPaths;
	}

	public function getSavables() {
		return $this->savables;
	}
	
	public function setSavables(array $savables) {
		ArgUtils::valArray($savables, 'rocket\spec\ei\manage\gui\Savable');
		$this->savables = $savables;
	}
	
	public function save() {
		$this->ensureInitialized();
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->onSave($this);
		}
		
		foreach ($this->savables as $savable) {
			$savable->save();
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
}


class EditableWrapper {
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