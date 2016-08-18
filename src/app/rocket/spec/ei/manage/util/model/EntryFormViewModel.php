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
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\EiState;
use n2n\dispatch\map\PropertyPath;
use n2n\util\ex\IllegalStateException;
use n2n\dispatch\map\PropertyPathPart;
use n2n\ui\view\impl\html\HtmlView;
use n2n\dispatch\map\MappingResult;
use rocket\spec\ei\manage\EntryGui;

class EntryFormViewModel {
	private $entryForm;
	private $entryFormPropertyPath;
	
	public function __construct(PropertyPath $entryFormPropertyPath) {
		$this->entryFormPropertyPath = $entryFormPropertyPath;
	}
	
	public function initFromView(HtmlView $view) {
		$mappingResult = $view->getFormHtmlBuilder()->meta()->getMapValue($this->entryFormPropertyPath);
		$view->assert($mappingResult instanceof MappingResult);
		
		$entryForm = $mappingResult->getObject();
		$view->assert($entryForm instanceof EntryForm);
		
		$this->entryForm = $entryForm;
	}
	
	public function getEntryForm(): EntryForm {
		if ($this->entryForm === null) {
			throw new IllegalStateException('EntryFormViewModel not initialized.');	
		}
		
		return $this->entryForm;
	}
	
	public function getEntryFormPropertyPath() {
		return $this->entryFormPropertyPath;
	}
	
	public function isTypeChangable() {
		return $this->getEntryForm()->isChoosable();
	}
	
	public function getTypeChoicesMap() {
		return $this->getEntryForm()->getChoicesMap();
	}
	
	public function createEditView() {
		$entryForm = $this->getEntryForm();
		IllegalStateException::assertTrue(!$entryForm->isChoosable());
		
		$eiSpecId = $entryForm->getChosenId();
		$entryModelForm = $entryForm->getChosenEntryModelForm();
		$propertyPath = $this->entryFormPropertyPath->ext(
						new PropertyPathPart('entryModelForms', true, $eiSpecId))->ext('dispatchable');
		$entryGuiModel = $entryModelForm->getEntryGuiModel();
		$eiMask = $entryGuiModel->getEiMask();
		
		return $eiMask->createBulkyView($entryForm->getEiState(), new EntryGui($entryGuiModel, $propertyPath));
		
	}
	
	public function createEditViews() {
		$entryForm = $this->getEntryForm();
		IllegalStateException::assertTrue($entryForm->isChoosable());
	
		$editViews = array();
		foreach ($entryForm->getEntryModelForms() as $eiSpecId => $entryModelForm) {
			$propertyPath = $this->entryFormPropertyPath->ext(
					new PropertyPathPart('entryModelForms', true, $eiSpecId))->ext('dispatchable');
			
			$entryGuiModel = $entryModelForm->getEntryGuiModel();
			$eiMask = $entryGuiModel->getEiMask();
			
			$editViews[$eiSpecId] = $eiMask->createBulkyView($entryForm->getEiState(), 
					new EntryGui($entryGuiModel, $propertyPath));
		}
		return $editViews;
	}
	
// 	private function buildTypeHtmlClasses(EiSpec $eiSpec, array $htmlClasses) {
// 		$htmlClasses[] = 'rocket-script-type-' . $eiSpec->getId();
// 		foreach ($eiSpec->getSubEiSpecs() as $sub) {
// 			$htmlClasses = $this->buildTypeHtmlClasses($sub, $htmlClasses);
// 		}
// 		return $htmlClasses;
// 	}
	
// 	public function createTypeLevelEditView($eiSpecId) {
// 		$entryFormParts = $this->entryForm->getLevelEntryFormParts();
// 		if (!isset($entryFormParts[$eiSpecId])) {
// 			throw new \InvalidArgumentException();
// 		}
		
// 		return $entryFormParts[$eiSpecId]->getGuiDefinition()->getEiMask()
// 				->createEditEntryView($entryFormParts[$eiSpecId], 
// 						$this->basePropertyPath->ext('levelEntryFormParts')->fieldExt($eiSpecId));
// 	}
}
