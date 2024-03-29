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

use n2n\util\type\ArgUtils;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\MagForm;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\field\GuiField;

class EiGuiValueBoundaryAssembler {
	private $guiDefinition;
	private $eiGuiValueBoundary;
	
	private $eiu;
	private $eiObjectForm;
	private $displayables = array();
	private $magPropertyPaths = array();
	
	private $guiFieldForks = array();
	private $forkedPropertyPaths = array();
	
	public function __construct(EiGuiValueBoundary $eiGuiValueBoundary) {
		$this->guiDefinition = $eiGuiValueBoundary->getEiGuiDeclaration()->getGuiDefinition();
		$this->eiGuiValueBoundary = $eiGuiValueBoundary;
		$this->eiu = new Eiu($eiGuiValueBoundary);
	}
	
	/**
	 * @return EiGuiValueBoundary
	 */
	public function getEiGuiValueBoundary(): EiGuiValueBoundary {
		return $this->eiGuiValueBoundary;
	}
	
//	/**
//	 * @return \n2n\impl\web\dispatch\mag\model\MagForm
//	 */
//	private function getOrCreateDispatchable() {
//		if ($this->eiObjectForm === null) {
//			$this->eiObjectForm = new MagForm(new MagCollection());
//			$this->eiGuiValueBoundary->setDispatchable($this->eiObjectForm);
//		}
//
//		return $this->eiObjectForm;
//	}
	
// 	public function save() {
// 		foreach ($this->savables as $savable) {
// 			$savable->save();
// 		}
		
// 		foreach ($this->forkedGuiFields as $id => $forkedGuiField) {
// 			if ($forkedGuiField->isReadOnly()) continue;
			
// 			$forkedGuiField->getEditable()->save();
// 		}
// 	}
	

	private function assembleExlGuiField(EiPropPath $eiPropPath, GuiPropWrapper $guiPropWrapper, DefPropPath $defPropPath): ?GuiField {
		return $guiPropWrapper->buildGuiField($this->eiGuiValueBoundary);
	}

	/**
	 * @param DefPropPath $defPropPath
	 * @param GuiPropFork $guiPropFork
	 * @return NULL|GuiField
	 */
	private function assembleGuiPropFork(DefPropPath $defPropPath, GuiPropFork $guiPropFork): ?GuiField {
		$eiPropPath = $defPropPath->getFirstEiPropPath();
		$eiPropPathStr = (string) $eiPropPath;
		
		$relativeDefPropPath = $defPropPath->getShifted();
		$guiFieldFork = null;
		if (isset($this->guiFieldForks[$eiPropPathStr])) {
			$guiFieldFork = $this->guiFieldForks[$eiPropPathStr];
		} else {
			$guiFieldFork = $this->guiFieldForks[$eiPropPathStr] = $guiPropFork->createGuiFieldFork(
					new Eiu($this->eiu, $eiPropPath));
		} 
		
		return $guiFieldFork->assembleGuiField($relativeDefPropPath);
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return GuiField
	 */
	public function assembleGuiField(DefPropPath $defPropPath) {
		if ($this->eiGuiValueBoundary->containsGuiFieldDefPropPath($defPropPath)) {
			return $this->eiGuiValueBoundary->getGuiFieldAssembly($defPropPath);
		}
		
		$guiField = null;
		if ($defPropPath->hasMultipleEiPropPaths()) {
			$guiField = $this->assembleGuiPropFork($defPropPath,
					$this->guiDefinition->getGuiPropFork($defPropPath->getFirstEiPropPath()));
		} else {
			$eiPropPath = $defPropPath->getFirstEiPropPath();
			$guiField = $this->assembleExlGuiField($eiPropPath, $this->guiDefinition->getGuiPropWrapper($eiPropPath), 
					$defPropPath);
		}
		
		if ($guiField !== null) {
			$this->eiGuiValueBoundary->putGuiField($defPropPath, $guiField);
		}
		
		return $guiField;
	}
	
// 	public function getDispatchable() {
// 		return $this->eiObjectForm;
// 	}
	
	public function finalize() {
		foreach ($this->guiFieldForks as $id => $guiFieldFork) {
			$this->eiGuiValueBoundary->putGuiFieldFork(new DefPropPath([EiPropPath::create($id)]), $guiFieldFork);
		}
		
		$this->eiGuiValueBoundary->markInitialized();
	}
}
