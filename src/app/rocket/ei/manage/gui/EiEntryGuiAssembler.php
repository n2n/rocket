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
	
	private $forkedGuiFields = array();
	private $forkedPropertyPaths = array();
	
	public function __construct(EiEntryGui $eiEntryGui) {
		$this->guiDefinition = $eiEntryGui->getEiGui()->getEiGuiViewFactory()->getGuiDefinition();
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
	 * @param string $id
	 * @param GuiProp $guiProp
	 * @return NULL|\rocket\ei\manage\gui\GuiFieldAssembly
	 */
	private function assembleGuiProp($id, GuiProp $guiProp) {
		$eiPropPath = $this->guiDefinition->getLevelEiPropPathById($id);
		$guiField = $guiProp->buildGuiField(new Eiu($this->eiu->entryGui(), $this->eiu->entry()->field($eiPropPath)));
		ArgUtils::valTypeReturn($guiField, GuiField::class, $guiProp, 'buildGuiField', true);
		
		if ($guiField === null) return null;
	
		$eiFieldWrapper = $this->eiu->entry()->getEiFieldWrapper($eiPropPath);
		
		if ($this->eiu->gui()->isReadOnly() || $guiField->isReadOnly()) {
			return new GuiFieldAssembly($guiProp, $guiField, $eiFieldWrapper);
		}
		
		$editable = $guiField->getEditable();
		ArgUtils::valTypeReturn($editable, GuiFieldEditable::class, $guiField, 'getEditable');
		$magWrapper = $this->getOrCreateDispatchable()->getMagCollection()->addMag($id, $editable->getMag($id));
		
		$magPropertyPath = new PropertyPath(array(new PropertyPathPart($id)));
		return new GuiFieldAssembly($guiProp, $guiField, $eiFieldWrapper, new MagAssembly($editable->isMandatory(), $magPropertyPath, $magWrapper),
				$editable);
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @param GuiPropFork $guiPropFork
	 * @param EiPropPath $eiPropPath
	 * @return NULL|\rocket\ei\manage\gui\GuiFieldAssembly
	 */
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
		if ($result === null) {
			return null;
		}
		
		$guiProp = $result->getGuiProp();
		$displayable = $result->getDisplayable();
		$eiFieldWrapper = $result->getEiFieldWrapper();
		$magAssembly = $result->getMagAssembly();
		
		if ($this->eiu->gui()->isReadOnly() || $displayable->isReadOnly() || $magAssembly === null) {
			return new GuiFieldAssembly($guiProp, $displayable, $eiFieldWrapper);
		}
		
		$magWrapper = null;
		if (!isset($this->forkedPropertyPaths[$id])) {
			$this->forkedPropertyPaths[$id] = new PropertyPath(array(new PropertyPathPart($id)));
		}
		
		/* $this->forkMagWrappers[$id] */
		return new GuiFieldAssembly($guiProp, $displayable, $eiFieldWrapper, 
				new MagAssembly($magAssembly->isMandatory(), 
						$this->forkedPropertyPaths[$id]->ext($magAssembly->getMagPropertyPath()), 
						$magAssembly->getMagWrapper()),
				$result->getSavable());
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return \rocket\ei\manage\gui\GuiFieldAssembly
	 */
	public function assembleGuiField(GuiIdPath $guiIdPath) {
		if ($this->eiEntryGui->containsGuiFieldGuiIdPath($guiIdPath)) {
			return $this->eiEntryGui->getGuiFieldAssembly($guiIdPath);
		}
		
		$guiFieldAssembly = null;
		if ($guiIdPath->hasMultipleIds()) {
			$guiFieldAssembly = $this->assembleGuiPropFork($guiIdPath,
					$this->guiDefinition->getLevelGuiPropForkById($guiIdPath->getFirstId()),
					$this->guiDefinition->getLevelEiPropPathById($guiIdPath->getFirstId()));
		} else {
			$guiFieldAssembly = $this->assembleGuiProp($guiIdPath->getFirstId(), 
					$this->guiDefinition->getLevelGuiPropById($guiIdPath->getFirstId()));
		}
		
		if ($guiFieldAssembly !== null) {
			$this->eiEntryGui->putGuiFieldAssembly($guiIdPath, $guiFieldAssembly);
		}
		
		return $guiFieldAssembly;
	}
	
// 	public function getDispatchable() {
// 		return $this->eiObjectForm;
// 	}
	
	public function finalize() {
		foreach ($this->forkedGuiFields as $id => $forkedGuiField) {
			$guiFieldForkEditable = $forkedGuiField->assembleGuiFieldFork();
			
			if ($guiFieldForkEditable === null) {
				$this->eiEntryGui->putGuiFieldForkAssembly(new GuiIdPath([$id]), new GuiFieldForkAssembly(array()));
				continue;
			}
			
			$forkMagWrapper = $this->getOrCreateDispatchable()->getMagCollection()
					->addMag($id, $guiFieldForkEditable->getForkMag());
			
			$forkMagAssemblies = array(new MagAssembly($guiFieldForkEditable->isForkMandatory(), 
					$this->forkedPropertyPaths[$id], $forkMagWrapper));
			
			$inheritForkMagAssemblies = $guiFieldForkEditable->getInheritForkMagAssemblies();
			ArgUtils::valArrayReturn($inheritForkMagAssemblies, $guiFieldForkEditable, 'getInheritForkMagAssemblies', MagAssembly::class);
			foreach ($inheritForkMagAssemblies as $forkMagAssembly) {
				$forkMagAssemblies[] = new MagAssembly($forkMagAssembly->isManadtory(),
						$this->forkedPropertyPaths[$id]->ext($propertyPath), $forkMagAssembly->getMagWrapper());
			}
			
			$this->eiEntryGui->putGuiFieldForkAssembly(new GuiIdPath([$id]), 
					new GuiFieldForkAssembly($forkMagAssemblies, $guiFieldForkEditable));
		}
		
		$this->eiEntryGui->markInitialized();
	}
}
