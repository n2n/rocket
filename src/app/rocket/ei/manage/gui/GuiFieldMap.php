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

use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\EiPropPath;
use n2n\util\ex\IllegalStateException;

class GuiFieldMap {
// 	private $eiEntryGui;
// 	private $forkGuiFieldPath;
	/**
	 * @var GuiField[]
	 */
	private $guiFields = array();
	
	function __construct(/*EiEntryGui $eiEntryGui, GuiFieldPath $forkGuiFieldPath*/) {
// 		$this->eiEntryGui = $eiEntryGui;
// 		$this->forkGuiFieldPath = $forkGuiFieldPath;
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
		$this->rAllGuiFields($guiFields, $this, new GuiFieldPath([]));
		return $guiFields;
	}
	
	/**
	 * @param GuiField[] $guiFields
	 * @param GuiFieldMap $guiFieldMap
	 * @param GuiFieldPath $parentGuiFieldPath
	 */
	private function rAllGuiFields(&$guiFields, $guiFieldMap, $parentGuiFieldPath) {
		foreach ($guiFieldMap->getGuiFields() as $eiPropPathStr => $guiField) {
			$guiFieldPath = $parentGuiFieldPath->ext($eiPropPathStr);
			
			$guiFields[(string) $guiFieldPath] = $guiField;
			
			if (null !== ($forkGuiFieldMap = $guiField->getForkGuiFieldMap())) {
				$this->rAllGuiFields($guiFields, $forkGuiFieldMap, $guiFieldPath);
			}
		}
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
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
	 * @param GuiFieldPath $guiFieldPath
	 * @return bool
	 */
	function containsEiPropPath(EiPropPath $eiPropPath) {
		return isset($this->guiFields[(string) $eiPropPath]);
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return bool
	 */
	function containsGuiFieldPath(GuiFieldPath $guiFieldPath) {
		return $this->rContainsGuiFieldPath($guiFieldPath->toArray(), $this->guiFields);
	}
	
	/**
	 * @param EiPropPath[] $eiPropPaths
	 * @param GuiFieldMap $guiFieldMap
	 * @return bool
	 */
	private function rContainsGuiFieldPath($eiPropPaths, $guiFieldMap) {
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
		
		return $this->rContainsGuiFieldPath($eiPropPaths, $forkGuiFieldMap);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\field\GuiFieldPath[]
	 */
	function getGuiFieldGuiFieldPaths() {
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
	function getGuiField(GuiFieldPath $guiFieldPath) {
		$guiFieldPathStr = (string) $guiFieldPath;
		if (!isset($this->guiFields[$guiFieldPathStr])) {
			throw new GuiException('No GuiField with GuiFieldPath \'' . $guiFieldPathStr . '\' for \'' . $this . '\' registered');
		}
		
		return $this->guiFields[$guiFieldPathStr];
	}
	
	function save() {
		foreach ($this->guiFields as $guiFieldPathStr => $guiField) {
			if (!$guiField->getSiField()->isReadOnly()
					/*&& $this->eiEntry->getEiEntryAccess()->isEiPropWritable(EiPropPath::create($eiPropPathStr))*/) {
				$guiField->save();
			}
		}
	}
	
	
	
}