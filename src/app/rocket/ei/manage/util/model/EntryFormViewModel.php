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
namespace rocket\ei\manage\util\model;

use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\map\PropertyPathPart;
use n2n\impl\web\ui\view\html\HtmlView;

class EiuEntryFormViewModel {
	private $eiuEntryForm;
	private $eiuEntryFormPropertyPath;
	private $groupRequired = false;
	
	public function __construct(EiuEntryForm $eiuEntryForm, bool $groupRequired = false) {
		$this->eiuEntryForm = $eiuEntryForm;
		$this->groupRequired = $groupRequired;
	}
	
	/**
	 * @return boolean
	 */
	public function isGroupRequired() {
		return $this->groupRequired;
	}
	
// 	public function initFromView(HtmlView $view) {
// 		$mappingResult = $view->getFormHtmlBuilder()->meta()->getMapValue($this->eiuEntryFormPropertyPath);
// 		$view->assert($mappingResult instanceof MappingResult);
		
// 		$eiuEntryForm = $mappingResult->getObject();
// 		$view->assert($eiuEntryForm instanceof EiuEntryForm);
		
// 		$this->eiuEntryForm = $eiuEntryForm;
// 	}
	
	public function getEiuEntryForm(): EiuEntryForm {
		if ($this->eiuEntryForm === null) {
			throw new IllegalStateException('EiuEntryFormViewModel not initialized.');	
		}
		
		return $this->eiuEntryForm;
	}
	
	public function getEiuEntryFormPropertyPath() {
		return $this->eiuEntryForm->getChosenEiuEntryTypeForm()->getEiuEntryGui()->getContextPropertyPath();
	}
	
	public function isTypeChangable() {
		return $this->getEiuEntryForm()->isChoosable();
	}
	
	public function getTypeChoicesMap() {
		return $this->getEiuEntryForm()->getChoicesMap();
	}
	
	public function getIconTypeMap() {
		$iconTypeMap = array();
		
		foreach ($this->eiuEntryForm->getEiuEntryTypeForms() as $eiTypeId => $eiuEntryTypeForm) {
			$iconTypeMap[$eiTypeId] = $eiuEntryTypeForm->getEiuEntryGui()->getEiuEntry()->getGenericIconType();
		}
		
		return $iconTypeMap;
	}
	
	public function createEditView(HtmlView $contextView) {
		$eiuEntryForm = $this->getEiuEntryForm();
		IllegalStateException::assertTrue(!$eiuEntryForm->isChoosable());
		
		$eiuEntryTypeForm = $eiuEntryForm->getChosenEiuEntryTypeForm();
		
		if (null !== ($contextPropertyPath = $this->eiuEntryForm->getContextPropertyPath())) {
			$eiTypeId = $eiuEntryForm->getChosenId();
			$eiuEntryTypeForm->getEiuEntryGui()->setContextPropertyPath($contextPropertyPath
					->ext(new PropertyPathPart('eiuEntryTypeForms', true, $eiTypeId))->ext('dispatchable'));
		}
		
		if ($this->groupRequired) {
			$eiuEntryTypeForm->getEiuEntryGui()->getEiuGui()->forceRootGroups();
		}
				
		return $eiuEntryTypeForm->getEiuEntryGui()->createView($contextView);
	}
	
	public function createEditViews(HtmlView $contextView) {
		$eiuEntryForm = $this->getEiuEntryForm();
		IllegalStateException::assertTrue($eiuEntryForm->isChoosable());
	
		$contextPropertyPath = $this->eiuEntryForm->getContextPropertyPath();
		
		$editViews = array();
		foreach ($eiuEntryForm->getEiuEntryTypeForms() as $eiTypeId => $eiuEntryTypeForm) {
			if ($contextPropertyPath !== null) {
				$eiuEntryTypeForm->getEiuEntryGui()->setContextPropertyPath($contextPropertyPath->ext(
						new PropertyPathPart('eiuEntryTypeForms', true, $eiTypeId))->ext('dispatchable'));
			}
			
			$eiuEntryGui = $eiuEntryTypeForm->getEiuEntryGui();
			if ($eiuEntryGui->hasForkMags()) {
				$eiuEntryGui->getEiuGui()->forceRootGroups();
			}
			
			$editViews[$eiTypeId] = $eiuEntryGui->createView($contextView, false);
		}
		return $editViews;
	}
	
// 	private function buildTypeHtmlClasses(EiType $eiType, array $htmlClasses) {
// 		$htmlClasses[] = 'rocket-script-type-' . $eiType->getId();
// 		foreach ($eiType->getSubEiTypes() as $sub) {
// 			$htmlClasses = $this->buildTypeHtmlClasses($sub, $htmlClasses);
// 		}
// 		return $htmlClasses;
// 	}
	
// 	public function createTypeLevelEditView($eiTypeId) {
// 		$eiuEntryFormParts = $this->eiuEntryForm->getLevelEiuEntryFormParts();
// 		if (!isset($eiuEntryFormParts[$eiTypeId])) {
// 			throw new \InvalidArgumentException();
// 		}
		
// 		return $eiuEntryFormParts[$eiTypeId]->getGuiDefinition()->getEiMask()
// 				->createEditEntryView($eiuEntryFormParts[$eiTypeId], 
// 						$this->basePropertyPath->ext('levelEiuEntryFormParts')->fieldExt($eiTypeId));
// 	}
}
