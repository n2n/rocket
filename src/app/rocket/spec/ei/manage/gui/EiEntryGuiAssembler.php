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
use n2n\web\dispatch\mag\MagCollection;
use n2n\web\dispatch\map\PropertyPathPart;
use n2n\impl\web\dispatch\mag\model\MagForm;
use rocket\spec\ei\manage\util\model\EiuEntryGui;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\mapping\EiFieldWrapper;
use rocket\spec\ei\EiPropPath;

class EiEntryGuiAssembler implements Savable {
	private $guiDefinition;
	private $eiEntryGui;
	
	private $eiu;
	private $eiObjectForm;
	private $displayables = array();
	private $magPropertyPaths = array();
	private $savables = array();
	
	private $forkedGuiFields = array();
	private $forkedPropertyPaths = array();
	private $forkMagWrappers = array();
	
	public function __construct(EiEntryGui $eiEntryGui) {
		$this->guiDefinition = $eiEntryGui->getEiGui()->getEiGuiViewFactory()->getGuiDefinition();
		$this->eiEntryGui = $eiuEntryGui;
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
	
	private function assembleGuiProp($id, GuiProp $guiProp) {
		$eiPropPath = $this->guiDefinition->getLevelEiPropPathById($id);
		$guiField = $guiProp->buildGuiField(new Eiu($this->eiu->entryGui(), $this->eiu->entry()->field($eiPropPath)));
		ArgUtils::valTypeReturn($guiField, GuiField::class, $guiProp, 'buildGuiField', true);
		
		if ($guiField === null) return null;
	
		$eiFieldWrapper = $this->eiu->entry()->getEiFieldWrapper($eiPropPath);
		
		if ($this->eiuEntryGui->isReadOnly() || $guiField->isReadOnly()) {
			return new GuiFieldAssembly($guiField, $eiFieldWrapper);
		}
		
		$editable = $guiField->getEditable();
		ArgUtils::valTypeReturn($editable, GuiFieldEditable::class, $guiField, 'getEditable');
		$magWrapper = $this->getOrCreateDispatchable()->getMagCollection()->addMag($id, $editable->getMag($id));
		$this->savables[$id] = $editable;
		
		$magPropertyPath = new PropertyPath(array(new PropertyPathPart($id)));
		return new GuiFieldAssembly($guiField, $eiFieldWrapper, new MagAssembly($editable->isMandatory(), $magPropertyPath, $magWrapper));
	}
	
	private function assembleGuiPropFork(GuiIdPath $guiIdPath, GuiPropFork $guiPropFork, EiPropPath $eiPropPath) {
		$id = $guiIdPath->getFirstId();
		
		$relativeGuiIdPath = $guiIdPath->getShifted();
		$forkedGuiField = null;
		if (isset($this->forkedGuiFields[$id])) {
			$forkedGuiField = $this->forkedGuiFields[$id];
		} else {
			$forkedGuiField = $this->forkedGuiFields[$id] = $guiPropFork->createGuiFieldFork(
					new Eiu($this->eiu->entryGui(), $this->eiu->entry()->field($eiPropPath)));
		} 
		
		$result = $forkedGuiField->assembleGuiField($relativeGuiIdPath);
		$displayable = $result->getDisplayable();
		$eiFieldWrapper = $result->getEiFieldWrapper();
		$magAssembly = $result->getMagAssembly();
		
		if ($this->eiuEntryGui->isReadOnly() || $displayable->isReadOnly() || $magAssembly === null) {
			return new GuiFieldAssembly($displayable, $eiFieldWrapper);
		}
		
		$magWrapper = null;
		if (!isset($this->forkedPropertyPaths[$id])) {
			$this->forkMagWrappers[$id] = $this->getOrCreateDispatchable()->getMagCollection()
					->addMag($id, $forkedGuiField->getEditable()->getForkMag());
			$this->forkedPropertyPaths[$id] = new PropertyPath(array(new PropertyPathPart($id)));
		}
		
		/* $this->forkMagWrappers[$id] */
		return new GuiFieldAssembly($displayable, $eiFieldWrapper, 
				new MagAssembly($magAssembly->isMandatory(), 
						$this->forkedPropertyPaths[$id]->ext($magAssembly->getMagPropertyPath()), 
						$magAssembly->getMagWrapper()));
	}
	
	public function assembleGuiField(GuiIdPath $guiIdPath) {
		if ($this->eiEntryGui->containsGuiFieldGuiIdPath($guiIdPath)) {
			return $this->eiEntryGui->getGuiField($guiIdPath);
		}
		
		if ($guiIdPath->hasMultipleIds()) {
			return $this->assembleGuiPropFork($guiIdPath,
					$this->guiDefinition->getLevelGuiPropForkById($guiIdPath->getFirstId()),
					$this->guiDefinition->getLevelEiPropPathById($guiIdPath->getFirstId()));
		}
		
		return $this->assembleGuiProp($guiIdPath->getFirstId(), $this->guiDefinition
				->getLevelGuiPropById($guiIdPath->getFirstId()));
	}
	
// 	public function getDispatchable() {
// 		return $this->eiObjectForm;
// 	}
	
	private function createGuiFieldForkAssemblies() {
		$guiFieldForkAssemblies = array();
		
		foreach ($this->forkedGuiFields as $id => $forkedGuiField) {
			if ($forkedGuiField->isReadOnly()) {
				$guiFieldForkAssemblies[] = new GuiFieldForkAssembly(array());
				continue;
			}
			
			$guiFieldForkEditable = $forkedGuiField->getEditable();
			
			$fromMagAssemblies = array(new MagAssembly($guiFieldFork->isForkMandatory(), $this->forkedPropertyPaths[$id], $this->forkMagWrappers[$id]));
			$inheritForkMagAssemblies = $guiFieldForkEditable->getInheritForkMagAssemblies();
			ArgUtils::valArrayReturn($inheritForkMagAssemblies, $guiFieldForkEditable, 'getInheritForkMagAssemblies', MagAssembly::class);
			
			foreach ($inheritForkMagAssemblies as $forkMagAssembly) {
				$fromMagAssemblies[] = new MagAssembly($forkMagAssembly->isManadtory(),
						$this->forkedPropertyPaths[$id]->ext($propertyPath), $forkMagAssembly->getMagWrapper());
			}
			
			$guiFieldForkAssemblies[] = new GuiFieldForkAssembly($fromMagAssemblies, $guiFieldForkEditable);
		}
		
		return $fromMagAssemblies;
	}
	
	public function finalize() {
		foreach ($this->createGuiFieldForkAssemblies() as $guiFieldForkAssembly) {
			$this->eiEntryGui->putGuiFieldForkAssembly($guiFieldForkAssembly);
		}
			
		$this->eiEntryGui->markInitialized();
	}
}
