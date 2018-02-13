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

class GuiFieldAssembler implements Savable {
	private $guiDefinition;
	private $eiuEntryGui;
	private $eiu;
	private $eiObjectForm;
	private $displayables = array();
	private $magPropertyPaths = array();
	private $savables = array();
	
	private $forkedGuiFields = array();
	private $forkedPropertyPaths = array();
	private $forkMagWrappers = array();
	
	public function __construct(GuiDefinition $guiDefinition, EiuEntryGui $eiuEntryGui) {
		$this->guiDefinition = $guiDefinition;
		$this->eiuEntryGui = $eiuEntryGui;
		$this->eiu = new Eiu($eiuEntryGui);
	}
	
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuEntryGui
	 */
	public function getEiuEntryGui() {
		return $this->eiu->entryGui();
	}
	
	private function getOrCreateDispatchable() {
		if ($this->eiObjectForm === null) {
			$this->eiObjectForm = new MagForm(new MagCollection());
		}
		
		return $this->eiObjectForm;
	}
	
	public function save() {
		foreach ($this->savables as $savable) {
			$savable->save();
		}
		
		foreach ($this->forkedGuiFields as $id => $forkedGuiField) {
			if ($forkedGuiField->isReadOnly()) continue;
			
			$forkedGuiField->getEditable()->save();
		}
	}
	
	private function assembleGuiProp($id, GuiProp $guiProp) {
		$eiPropPath = $this->guiDefinition->getLevelEiPropPathById($id);
		$guiField = $guiProp->buildGuiField(new Eiu($this->eiu->entryGui(), $this->eiu->entry()->field($eiPropPath)));
		ArgUtils::valTypeReturn($guiField, GuiField::class, $guiProp, 'buildGuiField', true);
		
		if ($guiField === null) return null;
	
		$eiFieldWrapper = $this->eiu->entry()->getEiFieldWrapper($eiPropPath);
		
		if ($this->eiuEntryGui->isReadOnly() || $guiField->isReadOnly()) {
			return new AssembleResult($guiField, $eiFieldWrapper);
		}
		
		$editable = $guiField->getEditable();
		ArgUtils::valTypeReturn($editable, GuiFieldEditable::class, $guiField, 'getEditable');
		$magWrapper = $this->getOrCreateDispatchable()->getMagCollection()->addMag($id, $editable->getMag($id));
		$this->savables[$id] = $editable;
		
		$magPropertyPath = new PropertyPath(array(new PropertyPathPart($id)));
		return new AssembleResult($guiField, $eiFieldWrapper, new MagAssembly($editable->isMandatory(), $magPropertyPath, $magWrapper));
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
			return new AssembleResult($displayable, $eiFieldWrapper);
		}
		
		$magWrapper = null;
		if (!isset($this->forkedPropertyPaths[$id])) {
			$this->forkMagWrappers[$id] = $this->getOrCreateDispatchable()->getMagCollection()
					->addMag($id, $forkedGuiField->getEditable()->getForkMag());
			$this->forkedPropertyPaths[$id] = new PropertyPath(array(new PropertyPathPart($id)));
		}
		
		/* $this->forkMagWrappers[$id] */
		return new AssembleResult($displayable, $eiFieldWrapper, 
				new MagAssembly($magAssembly->isMandatory(), 
						$this->forkedPropertyPaths[$id]->ext($magAssembly->getMagPropertyPath()), 
						$magAssembly->getMagWrapper()));
	}
	
	public function assembleGuiField(GuiIdPath $guiIdPath) {
		if ($guiIdPath->hasMultipleIds()) {
			return $this->assembleGuiPropFork($guiIdPath,
					$this->guiDefinition->getLevelGuiPropForkById($guiIdPath->getFirstId()),
					$this->guiDefinition->getLevelEiPropPathById($guiIdPath->getFirstId()));
		}
		
		return $this->assembleGuiProp($guiIdPath->getFirstId(), $this->guiDefinition
				->getLevelGuiPropById($guiIdPath->getFirstId()));
	}
	
	public function getDispatchable() {
		return $this->eiObjectForm;
	}
	
	public function getForkedMagPropertyPaths() {
		$propertyPaths = array();
		foreach ($this->forkedGuiFields as $id => $forkedGuiField) {
			if ($forkedGuiField->isReadOnly()) continue;
			
			$propertyPaths[] = $this->forkedPropertyPaths[$id];
			foreach ($forkedGuiField->getEditable()->getAdditionalForkMagPropertyPaths() as $propertyPath) {
				$propertyPaths[] = $this->forkedPropertyPaths[$id]->ext($propertyPath);
			}
		}
		return $propertyPaths;
	}
	
	public function getSavables() {
		$savables = $this->savables;
		
		foreach ($this->forkedGuiFields as $id => $forkedGuiField) {
			if ($forkedGuiField->isReadOnly()) continue;
			
			$savables[] = $forkedGuiField->getEditable();
		}

		return $savables;
	}
}

class AssembleResult {
	private $displayable;
	private $eiFieldWrapper;
	private $magAssembly;
	
	public function __construct(Displayable $displayable, EiFieldWrapper $eiFieldWrapper = null, 
			MagAssembly $magAssembly = null) {
		$this->displayable = $displayable;
		$this->eiFieldWrapper = $eiFieldWrapper;
		$this->magAssembly = $magAssembly;
	}
	
	/**
	 * @return Displayable
	 */
	public function getDisplayable(): Displayable {
		return $this->displayable;
	}
	
	public function getEiFieldWrapper() {
		return $this->eiFieldWrapper;
	}
	
	/**
	 * @return MagAssembly|null
	 */
	public function getMagAssembly() {
		return $this->magAssembly;
	}
}