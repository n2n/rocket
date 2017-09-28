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

use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\map\PropertyPathPart;
use n2n\impl\web\ui\view\html\HtmlView;

class EntryFormViewModel {
	private $entryForm;
	private $entryFormPropertyPath;
	
	public function __construct(EntryForm $entryForm) {
		$this->entryForm = $entryForm;
		
	}
	
// 	public function initFromView(HtmlView $view) {
// 		$mappingResult = $view->getFormHtmlBuilder()->meta()->getMapValue($this->entryFormPropertyPath);
// 		$view->assert($mappingResult instanceof MappingResult);
		
// 		$entryForm = $mappingResult->getObject();
// 		$view->assert($entryForm instanceof EntryForm);
		
// 		$this->entryForm = $entryForm;
// 	}
	
	public function getEntryForm(): EntryForm {
		if ($this->entryForm === null) {
			throw new IllegalStateException('EntryFormViewModel not initialized.');	
		}
		
		return $this->entryForm;
	}
	
	public function getEntryFormPropertyPath() {
		return $this->entryForm->getChosenEntryTypeForm()->getEiuEntryGui()->getContextPropertyPath();
	}
	
	public function isTypeChangable() {
		return $this->getEntryForm()->isChoosable();
	}
	
	public function getTypeChoicesMap() {
		return $this->getEntryForm()->getChoicesMap();
	}
	
	public function createEditView(HtmlView $contextView) {
		$entryForm = $this->getEntryForm();
		IllegalStateException::assertTrue(!$entryForm->isChoosable());
		
		$entryTypeForm = $entryForm->getChosenEntryTypeForm();
		
		if (null !== ($contextPropertyPath = $this->entryForm->getContextPropertyPath())) {
			$eiTypeId = $entryForm->getChosenId();
			$entryTypeForm->getEiuEntryGui()->setContextPropertyPath($contextPropertyPath
					->ext(new PropertyPathPart('entryTypeForms', true, $eiTypeId))->ext('dispatchable'));
		}
				
		return $entryTypeForm->getEiuEntryGui()->createView($contextView);
	}
	
	public function createEditViews(HtmlView $contextView) {
		$entryForm = $this->getEntryForm();
		IllegalStateException::assertTrue($entryForm->isChoosable());
	
		$contextPropertyPath = $this->entryForm->getContextPropertyPath();
		
		$editViews = array();
		foreach ($entryForm->getEntryTypeForms() as $eiTypeId => $entryTypeForm) {
			if ($contextPropertyPath !== null) {
				$entryTypeForm->getEiuEntryGui()->setContextPropertyPath($contextPropertyPath->ext(
						new PropertyPathPart('entryTypeForms', true, $eiTypeId))->ext('dispatchable'));
			}
			
			$entryGuiModel = $entryTypeForm->getEiuEntryGui();
			
			$editViews[$eiTypeId] = $entryGuiModel->createView($contextView);
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
// 		$entryFormParts = $this->entryForm->getLevelEntryFormParts();
// 		if (!isset($entryFormParts[$eiTypeId])) {
// 			throw new \InvalidArgumentException();
// 		}
		
// 		return $entryFormParts[$eiTypeId]->getGuiDefinition()->getEiMask()
// 				->createEditEntryView($entryFormParts[$eiTypeId], 
// 						$this->basePropertyPath->ext('levelEntryFormParts')->fieldExt($eiTypeId));
// 	}
}
