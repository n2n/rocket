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

use n2n\util\type\ArgUtils;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\mag\MagCollection;
use n2n\web\dispatch\map\PropertyPathPart;
use n2n\impl\web\dispatch\mag\model\MagForm;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;

class EiEntryGuiAssembler {
	private $guiDefinition;
	private $eiEntryGui;
	
	private $eiu;
	private $eiObjectForm;
	private $displayables = array();
	private $magPropertyPaths = array();
	
	private $guiFieldForks = array();
	private $forkedPropertyPaths = array();
	
	public function __construct(EiEntryGui $eiEntryGui) {
		$this->guiDefinition = $eiEntryGui->getEiGui()->getGuiDefinition();
		$this->eiEntryGui = $eiEntryGui;
		$this->eiu = new Eiu($eiEntryGui);
	}
	
	/**
	 * @return EiEntryGui
	 */
	public function getEiEntryGui() {
		return $this->eiEntryGui;
	}
	
	/**
	 * @return \n2n\impl\web\dispatch\mag\model\MagForm
	 */
	private function getOrCreateDispatchable() {
		if ($this->eiObjectForm === null) {
			$this->eiObjectForm = new MagForm(new MagCollection());
			$this->eiEntryGui->setDispatchable($this->eiObjectForm);
		}
		
		return $this->eiObjectForm;
	}
	
// 	public function save() {
// 		foreach ($this->savables as $savable) {
// 			$savable->save();
// 		}
		
// 		foreach ($this->forkedGuiFields as $id => $forkedGuiField) {
// 			if ($forkedGuiField->isReadOnly()) continue;
			
// 			$forkedGuiField->getEditable()->save();
// 		}
// 	}
	
	/**
	 * @param string $propertyName
	 * @param GuiProp $guiProp
	 * @return NULL|\rocket\ei\manage\gui\GuiField
	 */
	private function assembleExlGuiField(EiPropPath $eiPropPath, GuiProp $guiProp, GuiFieldPath $guiFieldPath) {
		$guiField = $guiProp->buildGuiField(new Eiu($this->eiu, $eiPropPath, $guiFieldPath));
		ArgUtils::valTypeReturn($guiField, GuiField::class, $guiProp, 'buildGuiField', true);
		
		return $guiField;
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @param GuiPropFork $guiPropFork
	 * @param EiPropPath $guiFieldPath
	 * @return NULL|\rocket\ei\manage\gui\GuiField
	 */
	private function assembleGuiPropFork(GuiFieldPath $guiFieldPath, GuiPropFork $guiPropFork) {
		$eiPropPath = $guiFieldPath->getFirstEiPropPath();
		$eiPropPathStr = (string) $eiPropPath;
		
		$relativeGuiFieldPath = $guiFieldPath->getShifted();
		$guiFieldFork = null;
		if (isset($this->guiFieldForks[$eiPropPathStr])) {
			$guiFieldFork = $this->guiFieldForks[$eiPropPathStr];
		} else {
			$guiFieldFork = $this->guiFieldForks[$eiPropPathStr] = $guiPropFork->createGuiFieldFork(
					new Eiu($this->eiu, $eiPropPath));
		} 
		
		return $guiFieldFork->assembleGuiField($relativeGuiFieldPath);
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return \rocket\ei\manage\gui\GuiField
	 */
	public function assembleGuiField(GuiFieldPath $guiFieldPath) {
		if ($this->eiEntryGui->containsGuiFieldGuiFieldPath($guiFieldPath)) {
			return $this->eiEntryGui->getGuiFieldAssembly($guiFieldPath);
		}
		
		$guiField = null;
		if ($guiFieldPath->hasMultipleEiPropPaths()) {
			$guiField = $this->assembleGuiPropFork($guiFieldPath,
					$this->guiDefinition->getGuiPropFork($guiFieldPath->getFirstEiPropPath()));
		} else {
			$eiPropPath = $guiFieldPath->getFirstEiPropPath();
			$guiField = $this->assembleExlGuiField($eiPropPath, $this->guiDefinition->getGuiProp($eiPropPath), 
					$guiFieldPath);
		}
		
		if ($guiField !== null) {
			$this->eiEntryGui->putGuiField($guiFieldPath, $guiField);
		}
		
		return $guiField;
	}
	
// 	public function getDispatchable() {
// 		return $this->eiObjectForm;
// 	}
	
	public function finalize() {
		foreach ($this->guiFieldForks as $id => $guiFieldFork) {
			$this->eiEntryGui->putGuiFieldFork(new GuiFieldPath([EiPropPath::create($id)]), $guiFieldFork);
		}
		
		$this->eiEntryGui->markInitialized();
	}
}
