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
use rocket\spec\ei\EiPropPath;
use n2n\web\dispatch\mag\MagWrapper;
use rocket\spec\ei\manage\util\model\EiuEntryGui;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\mapping\EiFieldWrapper;

class GuiElementAssembler implements Savable {
	private $guiDefinition;
	private $eiuGui;
	private $eiu;
	private $eiObjectForm;
	private $displayables = array();
	private $magPropertyPaths = array();
	private $savables = array();
	
	private $forkedGuiElements = array();
	private $forkedPropertyPaths = array();
	private $forkMagWrappers = array();
	
	public function __construct(GuiDefinition $guiDefinition, EiuEntryGui $eiuGui) {
		$this->guiDefinition = $guiDefinition;
		$this->eiuGui = $eiuGui;
		$this->eiu = new Eiu($eiuGui);
	}
	
	/**
	 * @return \rocket\spec\ei\manage\gui\Eiu
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
	}
	
	private function assembleGuiProp($id, GuiProp $guiProp) {
		$eiPropPath = $this->guiDefinition->getLevelEiPropPathById($id);
		$guiElement = $guiProp->buildGuiElement(new Eiu($this->eiu->entryGui(), $this->eiu->entry()->field($eiPropPath)));
		ArgUtils::valTypeReturn($guiElement, GuiElement::class, $guiProp, 'buildGuiElement', true);
		
		if ($guiElement === null) return null;
	
		$eiFieldWrapper = $this->eiu->entry()->getEiFieldWrapper($eiPropPath);
		
		if ($this->eiuGui->isReadOnly() || $guiElement->isReadOnly()) {
			return new AssembleResult($guiElement, $eiFieldWrapper);
		}
		
		$editable = $guiElement->getEditable();
		ArgUtils::valTypeReturn($editable, 'rocket\spec\ei\manage\gui\Editable', $guiElement, 'createEditable');
		$magWrapper = $this->getOrCreateDispatchable()->getMagCollection()->addMag($editable->createMag($id));
		$this->savables[$id] = $editable;
		
		$magPropertyPath = new PropertyPath(array(new PropertyPathPart($id)));
		return new AssembleResult($guiElement, $eiFieldWrapper, $magWrapper, $magPropertyPath, $editable->isMandatory());
	}
	
	private function assembleGuiPropFork(GuiIdPath $guiIdPath, GuiPropFork $guiPropFork) {
		$id = $guiIdPath->getFirstId();
		
		$relativeGuiIdPath = $guiIdPath->getShifted();
		$forkedGuiElement = null;
		if (isset($this->forkedGuiElements[$id])) {
			$forkedGuiElement = $this->forkedGuiElements[$id];
		} else {
			$forkedGuiElement = $this->forkedGuiElements[$id] = $guiPropFork->createGuiElementFork($this->eiu);
		} 
		
		$result = $forkedGuiElement->assembleGuiElement($relativeGuiIdPath);
		$displayable = $result->getDisplayable();
		$eiFieldWrapper = $result->getEiFieldWrapper();
		$magPropertyPath = $result->getMagPropertyPath();
		
		
		if ($this->eiuGui->isReadOnly() || $displayable->isReadOnly() || $magPropertyPath === null) {
			return new AssembleResult($displayable, $eiFieldWrapper);
		}
		
		$magWrapper = null;
		if (!isset($this->forkedPropertyPaths[$id])) {
			$this->savables[$id] = $forkedGuiElement;
			$this->forkMagWrappers[$id] = $this->getOrCreateDispatchable()->getMagCollection()->addMag(
					$forkedGuiElement->buildForkMag($id));
			$this->forkedPropertyPaths[$id] = new PropertyPath(array(new PropertyPathPart($id)));
		}
		
		return new AssembleResult($displayable, $eiFieldWrapper, $this->forkMagWrappers[$id], 
				$this->forkedPropertyPaths[$id]->ext($magPropertyPath), $result->isMandatory());
	}
	
	public function assembleGuiElement(GuiIdPath $guiIdPath) {
		if ($guiIdPath->hasMultipleIds()) {
			return $this->assembleGuiPropFork($guiIdPath, $this->guiDefinition
					->getLevelGuiPropForkById($guiIdPath->getFirstId()));
		}
		
		return $this->assembleGuiProp($guiIdPath->getFirstId(), $this->guiDefinition
				->getLevelGuiPropById($guiIdPath->getFirstId()));
	}
	
	public function getDispatchable() {
		return $this->eiObjectForm;
	}
	
	public function getForkedMagPropertyPaths() {
		return $this->forkedPropertyPaths;
	}
	
	public function getSavables() {
		return $this->savables;
	}
}

class AssembleResult {
	private $displayable;
	private $eiFieldWrapper;
	private $magWrapper;
	private $magPropertyPath;
	private $mandatory;
// 	private $eiPropPath;
	
	public function __construct(Displayable $displayable, EiFieldWrapper $eiFieldWrapper = null, 
			MagWrapper $magWrapper = null, PropertyPath $magPropertyPath = null, bool $mandatory = null) {
		$this->displayable = $displayable;
		$this->eiFieldWrapper = $eiFieldWrapper;
		$this->magWrapper = $magWrapper;
		$this->magPropertyPath = $magPropertyPath;
		$this->mandatory = $mandatory;
		
		if ($magWrapper !== null && $magPropertyPath === null && $mandatory === null) {
			throw new \InvalidArgumentException();
		}
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
	 * @return \n2n\web\dispatch\mag\MagWrapper|null
	 */
	public function getMagWrapper() {
		return $this->magWrapper;
	}
	
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath|null
	 */
	public function getMagPropertyPath() {
		return $this->magPropertyPath;
	}
	
	/**
	 * @return bool
	 */
	public function isMandatory() {
		return $this->mandatory;
	}
}

// class EiObjectForm implements Dispatchable {
// 	private static function _annos(AnnoInit $ai) {
// 		$ai->p('magForm', new AnnoDispObject());
// 		$ai->p('forkedDispatchables', new AnnoDispObjectArray());
// 	}
	
// 	private $MagForm;
// 	private $forkedDispatchables = array();
	
// 	public function getMagForm() {
// 		return $this->MagForm;
// 	}
	
// 	public function setMagForm(MagForm $MagForm) {
// 		$this->MagForm = $MagForm;
// 	}
	
	
// 	public function getForkedDispatchables() {
// 		return $this->forkedDispatchables;
// 	}
	
// 	public function setForkedDispatchables(array $forkedDispatchables) {
// 		$this->forkedDispatchables = $forkedDispatchables;
// 	}
// }
