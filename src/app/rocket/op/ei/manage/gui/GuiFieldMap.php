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
namespace rocket\op\ei\manage\gui;

use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\field\GuiField;
use rocket\op\ei\EiPropPath;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;

class GuiFieldMap {
// 	private $eiGuiValueBoundary;
// 	private $forkDefPropPath;
	/**
	 * @var GuiField[]
	 */
	private $guiFields = array();
	
	function __construct(/*EiGuiValueBoundary $eiGuiValueBoundary, DefPropPath $forkDefPropPath*/) {
// 		$this->eiGuiValueBoundary = $eiGuiValueBoundary;
// 		$this->forkDefPropPath = $forkDefPropPath;
	}
	
	private function ensureNotInitialized() {
// 		if (!$this->eiGuiValueBoundary->isInitialized()) {
// 			return;
// 		}
		
// 		throw new IllegalStateException('EiGuiValueBoundary already initialized.');
	}
	
	/**
	 * @return GuiField[]
	 */
	function getGuiFields(): array {
		return $this->guiFields;
	}

	/**
	 * @return EiPropPath[]
	 */
	function getEiPropPaths(): array {
		return array_map(fn ($s) => EiPropPath::create($s), array_keys($this->guiFields));

	}

	/**
	 * @return GuiField[]
	 */
	function getAllGuiFields() {
		$guiFields = [];
		$this->rAllGuiFields($guiFields, $this, new DefPropPath([]));
		return $guiFields;
	}
	
	/**
	 * @param GuiField[] $guiFields
	 * @param GuiFieldMap $guiFieldMap
	 * @param DefPropPath $parentDefPropPath
	 */
	private function rAllGuiFields(&$guiFields, $guiFieldMap, $parentDefPropPath) {
		foreach ($guiFieldMap->getGuiFields() as $eiPropPathStr => $guiField) {
			$defPropPath = $parentDefPropPath->ext($eiPropPathStr);
			
			$guiFields[(string) $defPropPath] = $guiField;
			
			if (null !== ($forkGuiFieldMap = $guiField->getForkGuiFieldMap())) {
				$this->rAllGuiFields($guiFields, $forkGuiFieldMap, $defPropPath);
			}
		}
	}
	
	/**
	 * @param DefPropPath $defPropPath
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
	 * @param DefPropPath $defPropPath
	 * @return bool
	 */
	function containsEiPropPath(EiPropPath $eiPropPath) {
		return isset($this->guiFields[(string) $eiPropPath]);
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return bool
	 */
	function containsDefPropPath(DefPropPath $defPropPath): bool {
		return $this->rContainsDefPropPath($defPropPath->toArray(), $this);
	}
	
	/**
	 * @param EiPropPath[] $eiPropPaths
	 * @param GuiFieldMap $guiFieldMap
	 * @return bool
	 */
	private function rContainsDefPropPath($eiPropPaths, $guiFieldMap) {
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
		
		return $this->rContainsDefPropPath($eiPropPaths, $forkGuiFieldMap);
	}
	
//	/**
//	 * @return \rocket\op\ei\manage\DefPropPath[]
//	 */
//	function getEiPropPaths() {
//		$eiPropPaths = array();
//		foreach (array_keys($this->guiFields) as $eiPropPathStr) {
//			$eiPropPaths[] = EiPropPath::create($eiPropPathStr);
//		}
//		return $eiPropPaths;
//	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @throws GuiException
	 * @return GuiField
	 */
	function getGuiField(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->guiFields[$eiPropPathStr])) {
			throw new GuiException('No GuiField with EiPropPath \'' . $eiPropPathStr . '\' for GuiFieldMap registered');
		}
		
		return $this->guiFields[$eiPropPathStr];
	}
	
	function save(): void {
		foreach ($this->guiFields as $defPropPathStr => $guiField) {
			if (!$guiField->getSiField()->isReadOnly()
					/*&& $this->eiEntry->getEiEntryAccess()->isEiPropWritable(EiPropPath::create($eiPropPathStr))*/) {
				$guiField->save();
			}
		}
	}
}
