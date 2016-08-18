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
use n2n\dispatch\map\PropertyPath;
use n2n\dispatch\Dispatchable;

class EiSelectionGui {
	private $guiDefinition;
	private $viewMode;
	private $displayables = array();
	private $eiFieldPaths = array();
	private $eiSelectionGuiListeners = array();
	
	private $dispatchable;
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
	
	/**
	 * @return boolean
	 */
	public function isViewModeOverview() {
		return $this->viewMode == DisplayDefinition::VIEW_MODE_LIST_READ
				|| $this->viewMode == DisplayDefinition::VIEW_MODE_TREE_READ;
	}
	
	public function createPropertyPath(GuiIdPath $guiIdPath, PropertyPath $contextPropertyPath = null) {
		if ($contextPropertyPath !== null) {
			return $contextPropertyPath->ext((string) $guiIdPath);
		}
		
		return new PropertyPath(array((string) $guiIdPath));
	}
	
	public function putDisplayable(GuiIdPath $guiIdPath, Displayable $displayable) {
		$this->displayables[(string) $guiIdPath] = $displayable;
	}
	
	public function putEiFieldPath(GuiIdPath $guiIdPath, EiFieldPath $eiFieldPath) {
		$this->eiFieldPaths[(string) $guiIdPath] = $eiFieldPath;
	}
	
	public function containsDisplayable(GuiIdPath $guiIdPath) {
		return isset($this->displayables[(string) $guiIdPath]);
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
	
	public function getEiFieldPathByGuiIdPath(GuiIdPath $guiIdPath) {
		$guiIdPathStr = (string) $guiIdPath;
		
		if (isset($this->eiFieldPaths[$guiIdPathStr])) {
			return $this->eiFieldPaths[$guiIdPathStr];
		}
		
		return null;
	}

	public function getDispatchable() {
		return $this->dispatchable;
	}
	
	public function setDispatchable(Dispatchable $dispatchable = null) {
		$this->dispatchable = $dispatchable;
	}
	
	public function putEditableInfo(GuiIdPath $guiIdPath, EditableInfo $editableInfo) {
		$this->editableInfos[(string) $guiIdPath] = $editableInfo;
	}
	
	public function containsEditableInfoGuiIdPath(GuiIdPath $guiIdPath): bool {
		return isset($this->editableInfos[(string) $guiIdPath]);
	}
	
	public function getEditableInfoByGuiIdPath(GuiIdPath $guiIdPath): EditableInfo {
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
		ArgUtils::valArray($forkMagPropertyPaths, 'n2n\dispatch\map\PropertyPath');
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
		foreach ($this->eiSelectionGuiListeners as $eiSelectionGuiListener) {
			$eiSelectionGuiListener->onSave($this);
		}
		
		foreach ($this->savables as $savable) {
			$savable->save();
		}
		
		foreach ($this->eiSelectionGuiListeners as $eiSelectionGuiListener) {
			$eiSelectionGuiListener->saved($this);
		}
	}
	
	public function registerEiSelectionGuiListener(EiSelectionGuiListener $eiSelectionGuiListener) {
		$this->eiSelectionGuiListeners[spl_object_hash($eiSelectionGuiListener)] = $eiSelectionGuiListener;
	}
	
	public function unregisterEiSelectionGuiListener(EiSelectionGuiListener $eiSelectionGuiListener) {
		unset($this->eiSelectionGuiListeners[spl_object_hash($eiSelectionGuiListener)]);
	}
}


class EditableInfo {
	private $mandatory;
	private $magPropertyPath;
	
	public function __construct(bool $mandatory, PropertyPath $magPropertyPath) {
		$this->mandatory = $mandatory;
		$this->magPropertyPath = $magPropertyPath;
	}
	
	public function isMandatory(): bool {
		return $this->mandatory;
	}
	
	public function getMagPropertyPath(): PropertyPath {
		return $this->magPropertyPath;
	}
}
