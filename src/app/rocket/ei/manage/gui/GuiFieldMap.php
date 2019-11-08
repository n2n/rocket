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
		if (!$this->eiEntryGui->isInitialized()) {
			return;
		}
		
		throw new IllegalStateException('EiEntryGui already initialized.');
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @param GuiField $guiField
	 */
	function putGuiField(EiPropPath $eiFieldPath, GuiField $guiField) {
		$this->ensureNotInitialized();
		
		$key = (string) $guiFieldPath;
		
		if (isset($this->guiFields[$key])) {
			throw new IllegalStateException('GuiFieldPath already initialized: ' . $guiFieldPath);
		}
		
		$this->guiFields[$key] = $guiField;
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