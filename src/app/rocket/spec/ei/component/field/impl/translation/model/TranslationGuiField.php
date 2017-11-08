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
namespace rocket\spec\ei\component\field\impl\translation\model;

use n2n\web\dispatch\Dispatchable;
use rocket\spec\ei\manage\gui\GuiIdPath;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\gui\GuiFieldFork;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\gui\Displayable;
use rocket\spec\ei\manage\gui\AssembleResult;
use rocket\spec\ei\manage\gui\GuiFieldAssembler;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\component\field\impl\relation\model\ToManyEiField;
use rocket\spec\ei\component\field\impl\relation\model\RelationEntry;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use rocket\spec\ei\component\field\impl\translation\conf\N2nLocaleDef;
use rocket\spec\ei\manage\mapping\impl\EiFieldWrapperWrapper;

class TranslationGuiField implements GuiFieldFork {
	private $toManyEiField;
	private $guiDefinition;
	private $label;

	private $n2nLocaleDefs = array();
	private $targetRelationEntries = array();
	/**
	 * @var GuiFieldAssembler[]
	 */
	private $guiFieldAssemblers = array();
	private $mandatoryN2nLocaleIds = array();
	private $activeN2nLocaleIds = array();
	
	private $translationForm;
		
	public function __construct(ToManyEiField $toManyEiField, GuiDefinition $guiDefinition, $label, int $minNumTranslations) {
		$this->toManyEiField = $toManyEiField;
		$this->guiDefinition = $guiDefinition;
		$this->label = $label;
		$this->min = $minNumTranslations;
	}
	
	public function registerN2nLocale(N2nLocaleDef $n2nLocaleDef, RelationEntry $targetRelationEntry, 
			GuiFieldAssembler $guiFieldAssembler, $mandatory, $active) {
		$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
		$this->n2nLocaleDefs[$n2nLocaleId] = $n2nLocaleDef;
		$this->targetRelationEntries[$n2nLocaleId] = $targetRelationEntry;
		$this->guiFieldAssemblers[$n2nLocaleId] = $guiFieldAssembler;
		if ($mandatory) {
			$this->mandatoryN2nLocaleIds[$n2nLocaleId] = $n2nLocaleId;
		}
		if ($active) {
			$this->activeN2nLocaleIds[$n2nLocaleId] = $n2nLocaleId;
		}
	}
	
	private function setupTranslationForm() {
		if ($this->translationForm === null) {
			$this->translationForm = new TranslationForm($this->mandatoryN2nLocaleIds, $this->label);
		}

		foreach ($this->guiFieldAssemblers as $n2nLocaleId => $guiFieldAssebler) {
			$dispatchable = $guiFieldAssebler->getDispatchable();
			$guiFieldAssebler->getEiuEntryGui()->getEiuEntry()->isNew();
			if ($dispatchable !== null) {
				$this->translationForm->putAvailableDispatchable($n2nLocaleId, $dispatchable);
				
				if (isset($this->activeN2nLocaleIds[$n2nLocaleId])) {
					$this->translationForm->putDispatchable($n2nLocaleId, $dispatchable);
				}		
			}
		}
	}
	
	public function assembleGuiField(GuiIdPath $guiIdPath): AssembleResult {
		$label = $this->guiDefinition->getGuiPropByGuiIdPath($guiIdPath)->getDisplayLabel();
		$eiPropPath = $this->guiDefinition->guiIdPathToEiPropPath($guiIdPath);

// 		$fieldErrorInfo = new FieldErrorInfo();
		
		$translationDisplayable = new TranslationDisplayable($label, $this->n2nLocaleDefs);
		
		$translationMag = null;
		$eiFieldWrappers = array();
		
		$mandatory = false;
		foreach ($this->guiFieldAssemblers as $n2nLocaleId => $guiFieldAssembler) {
			$result = $guiFieldAssembler->assembleGuiField($guiIdPath);
			if ($result === null) continue;
			
			$fieldErrorInfo = $guiFieldAssembler->getEiuEntryGui()->getEiuEntry()->getEiEntry()->getMappingErrorInfo()
					->getFieldErrorInfo($eiPropPath);
			if (null !== ($eiFieldWrapper = $result->getEiFieldWrapper())) {
				$eiFieldWrappers[] = $eiFieldWrapper;
			}
// 			$fieldErrorInfo->addSubFieldErrorInfo($result->getFieldErrorInfo());
			
			if ($this->targetRelationEntries[$n2nLocaleId]->getEiObject()->isNew()) {
				$translationDisplayable->putDisplayable($n2nLocaleId, new EmptyDisplayable($result->getDisplayable()), 
						$fieldErrorInfo);
			} else {
				$translationDisplayable->putDisplayable($n2nLocaleId, $result->getDisplayable(), $fieldErrorInfo);
			}
			
			if ($guiFieldAssembler->getEiuEntryGui()->isReadOnly()) continue;
			
			if ($translationMag === null) {
				$translationMag = new TranslationMag($guiIdPath->__toString(), $label);
			}
			
			if (null !== ($magPropertyPath = $result->getMagPropertyPath())) {
				$translationMag->putMagPropertyPath($n2nLocaleId, $magPropertyPath, $fieldErrorInfo);
				if (!$mandatory) $mandatory = $result->isMandatory();
			} else {
				$translationMag->putDisplayable($n2nLocaleId, $result->getDisplayable(), $fieldErrorInfo);
			}
		}
		
		$eiFieldWrapperWrapper = new EiFieldWrapperWrapper($eiFieldWrappers);
		
		if ($translationMag === null) {
			return new AssembleResult($translationDisplayable, $eiFieldWrapperWrapper);
		}
		
		$this->setupTranslationForm();
				
		$magInfo = $this->translationForm->registerMag($translationMag);
		return new AssembleResult($translationDisplayable, $eiFieldWrapperWrapper, $magInfo['magWrapper'], $magInfo['propertyPath'], $mandatory);
	}
		
	public function buildForkMag(string $propertyName) {
		if ($this->translationForm === null) {
			return null;
		}
		
		return new ForkMag($this->label, $this->translationForm, $this->n2nLocaleDefs, $this->min);
	}
	
	public function save() {
		if ($this->translationForm === null) return;
		
		$targetRelationEntries = array();
		foreach ($this->translationForm->getDispatchables() as $n2nLocaleId => $dispatchable) {
			$this->guiFieldAssemblers[$n2nLocaleId]->save();
			$targetRelationEntries[$n2nLocaleId] = $this->targetRelationEntries[$n2nLocaleId];
			$targetRelationEntries[$n2nLocaleId]->getEiObject()->getLiveObject()
					->setN2nLocale(new N2nLocale($n2nLocaleId));
		}
		
		$this->toManyEiField->setValue($targetRelationEntries);
	}
}

class EmptyDisplayable implements Displayable {
	private $displayable;
	
	public function __construct(Displayable $displayable) {
		$this->displayable = $displayable;
	}
	
	public function getGroupType() {
		return $this->displayable->getGroupType();
	}
	
	public function isMandatory(): bool {
		return $this->displayable->isMandatory();
	}
	
	public function isReadOnly(): bool {
		return $this->displayable->isReadOnly();
	}
	
	public function getUiOutputLabel(): string {
		return $this->displayable->getUiOutputLabel();
	}
	
	public function getOutputHtmlContainerAttrs(): array {
		return array('class' => 'rocket-empty-translation');
	}
	
	public function createOutputUiComponent(HtmlView $view) {
		return $view->getHtmlBuilder()->getText('ei_impl_locale_not_active_label');
	}
}
