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

use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\EiPropPath;
use n2n\util\ex\IllegalStateException;

class GuiFieldMap {
// 	private $eiEntryGui;
// 	private $forkGuiPropPath;
	/**
	 * @var GuiField[]
	 */
	private $guiFields = array();
	
	function __construct(/*EiEntryGui $eiEntryGui, GuiPropPath $forkGuiPropPath*/) {
// 		$this->eiEntryGui = $eiEntryGui;
// 		$this->forkGuiPropPath = $forkGuiPropPath;
	}
	
	private function ensureNotInitialized() {
// 		if (!$this->eiEntryGui->isInitialized()) {
// 			return;
// 		}
		
// 		throw new IllegalStateException('EiEntryGui already initialized.');
	}
	
	/**
	 * @return GuiField[]
	 */
	function getGuiFields() {
		return $this->guiFields;
	}
	
	/**
	 * @return GuiField[]
	 */
	function getAllGuiFields() {
		$guiFields = [];
		$this->rAllGuiFields($guiFields, $this, new GuiPropPath([]));
		return $guiFields;
	}
	
	/**
	 * @param GuiField[] $guiFields
	 * @param GuiFieldMap $guiFieldMap
	 * @param GuiPropPath $parentGuiPropPath
	 */
	private function rAllGuiFields(&$guiFields, $guiFieldMap, $parentGuiPropPath) {
		foreach ($guiFieldMap->getGuiFields() as $eiPropPathStr => $guiField) {
			$guiPropPath = $parentGuiPropPath->ext($eiPropPathStr);
			
			$guiFields[(string) $guiPropPath] = $guiField;
			
			if (null !== ($forkGuiFieldMap = $guiField->getForkGuiFieldMap())) {
				$this->rAllGuiFields($guiFields, $forkGuiFieldMap, $guiPropPath);
			}
		}
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @param GuiField $guiField
	 */
	function putGuiField(EiPropPath $eiPropPath, GuiField $guiField) {
		$this->ensureNotInitialized();
		
		$key = (string) $eiPropPath;
		
		if (isset($this->guiFields[$key])) {
			throw new IllegalStateException('EiPropPath already initialized: ' . $key);
		}
		
		$this->guiFields[$key] = $guiField;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return bool
	 */
	function containsEiPropPath(EiPropPath $eiPropPath) {
		return isset($this->guiFields[(string) $eiPropPath]);
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return bool
	 */
	function containsGuiPropPath(GuiPropPath $guiPropPath) {
		return $this->rContainsGuiPropPath($guiPropPath->toArray(), $this->guiFields);
	}
	
	/**
	 * @param EiPropPath[] $eiPropPaths
	 * @param GuiFieldMap $guiFieldMap
	 * @return bool
	 */
	private function rContainsGuiPropPath($eiPropPaths, $guiFieldMap) {
		$eiPropPathStr = (string) array_shift($eiPropPaths);
		
		if (!isset($this->guiFields[$eiPropPathStr])) {
			return false;
		}
		
		if (empty($eiPropPaths)) {
			return true;
		}
		
		$forkGuiFieldMap = $this->guiFields[$eiPropPathStr]->getForkGuiFieldMap();
		if ($forkGuiFieldMap === null) {
			return false;
		}
		
		return $this->rContainsGuiPropPath($eiPropPaths, $forkGuiFieldMap);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\field\GuiPropPath[]
	 */
	function getEiPropPaths() {
		$eiPropPaths = array();
		foreach (array_keys($this->guiFields) as $eiPropPathStr) {
			$eiPropPaths[] = EiPropPath::create($eiPropPathStr);
		}
		return $eiPropPaths;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @throws GuiException
	 * @return GuiField
	 */
	function getGuiField(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->guiFields[$eiPropPathStr])) {
			throw new GuiException('No GuiField with EiPropPath \'' . $eiPropPathStr . '\' for \'' . $this . '\' registered');
		}
		
		return $this->guiFields[$eiPropPathStr];
	}
	
	function save() {
		foreach ($this->guiFields as $guiPropPathStr => $guiField) {
			if (!$guiField->getSiField()->isReadOnly()
					/*&& $this->eiEntry->getEiEntryAccess()->isEiPropWritable(EiPropPath::create($eiPropPathStr))*/) {
				$guiField->save();
			}
		}
	}
}
