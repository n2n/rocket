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
namespace rocket\impl\ei\component\prop\translation\model;

use rocket\ei\manage\gui\GuiIdPath;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\Displayable;
use rocket\ei\manage\gui\GuiFieldAssembly;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\prop\relation\model\ToManyEiField;
use rocket\impl\ei\component\prop\relation\model\RelationEntry;
use rocket\impl\ei\component\prop\translation\conf\N2nLocaleDef;
use rocket\ei\manage\mapping\impl\EiFieldWrapperWrapper;
use n2n\util\uri\Url;
use n2n\web\dispatch\mag\Mag;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\GuiFieldForkEditable;
use rocket\ei\manage\gui\MagAssembly;
use rocket\ei\manage\gui\GuiFieldFork;
use n2n\impl\web\ui\view\html\HtmlUtils;
use rocket\ei\util\gui\EiuEntryGuiAssembler;

class TranslationGuiFieldFork implements GuiFieldFork, GuiFieldForkEditable {
	private $toManyEiField;
	private $guiDefinition;
	private $label;
	private $min;

	private $n2nLocaleDefs = array();
	/**
	 * @var RelationEntry[]
	 */
	private $targetRelationEntries = array();
	/**
	 * @var EiuEntryGuiAssembler[]
	 */
	private $eiuEntryGuiAssemblers = array();
	private $mandatoryN2nLocaleIds = array();
	private $activeN2nLocaleIds = array();
	
	private $translationForm;
	private $markClassKey;
	private $srcUrl;
		
	public function __construct(ToManyEiField $toManyEiField, GuiDefinition $guiDefinition, $label, int $minNumTranslations) {
		$this->toManyEiField = $toManyEiField;
		$this->guiDefinition = $guiDefinition;
		$this->label = $label;
		$this->min = $minNumTranslations;
	}
	
	public function registerN2nLocale(N2nLocaleDef $n2nLocaleDef, RelationEntry $targetRelationEntry, 
			EiuEntryGuiAssembler $eiuEntryGuiAssembler, $mandatory, $active) {
		$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
		$this->n2nLocaleDefs[$n2nLocaleId] = $n2nLocaleDef;
		$this->targetRelationEntries[$n2nLocaleId] = $targetRelationEntry;
		$this->eiuEntryGuiAssemblers[$n2nLocaleId] = $eiuEntryGuiAssembler;
		if ($mandatory) {
			$this->mandatoryN2nLocaleIds[$n2nLocaleId] = $n2nLocaleId;
		}
		if ($active) {
			$this->activeN2nLocaleIds[$n2nLocaleId] = $n2nLocaleId;
		}
	}
	
	private function buildSrcLoadConfig(GuiIdPath $guiIdPath) {
		$loadUrls = array();
		$copyUrls = array();
		
		if ($this->srcUrl === null) {
			return new SrcLoadConfig($guiIdPath, $loadUrls, $copyUrls);
		}
		
		foreach ($this->targetRelationEntries as $n2nLocaleId => $targetRelationEntry) {
			$loadUrls[$n2nLocaleId] = $this->srcUrl->extR('live', array(
					'pid' => ($targetRelationEntry->isNew() ? null : $targetRelationEntry->getPid()), 
					'n2nLocale' => $n2nLocaleId));
			
			if ($targetRelationEntry->isNew()) continue;
				
			$copyUrls[$n2nLocaleId] = $this->srcUrl->extR('livecopy', 
					array('fromPid' => ($targetRelationEntry->getPid())));
		}
		
		return new SrcLoadConfig($guiIdPath, $loadUrls, $copyUrls);
	}
	
	private function setupTranslationForm() {
		if ($this->translationForm === null) {
			$this->translationForm = new TranslationForm($this->mandatoryN2nLocaleIds, $this->label);
		}

		foreach ($this->eiuEntryGuiAssemblers as $n2nLocaleId => $eiuEntryGuiAssembler) {
			$dispatchable = $eiuEntryGuiAssembler->getEiuEntryGui()->getDispatchable();
			$eiuEntryGuiAssembler->getEiuEntryGui()->getEiuEntry()->isNew();
			if ($dispatchable !== null) {
				$this->translationForm->putAvailableDispatchable($n2nLocaleId, $dispatchable);
				
				if (isset($this->activeN2nLocaleIds[$n2nLocaleId])) {
					$this->translationForm->putDispatchable($n2nLocaleId, $dispatchable);
				}		
			}
		}
	}
	
	private function getMarkClassKey() {
		if ($this->markClassKey !== null) {
			return $this->markClassKey;
		}
		
		return $this->markClassKey = HtmlUtils::buildUniqueId();
	}
	
	public function assembleGuiField(GuiIdPath $guiIdPath): ?GuiFieldAssembly {
		$label = $this->guiDefinition->getGuiPropByGuiIdPath($guiIdPath)->getDisplayLabel();
		$eiPropPath = $this->guiDefinition->guiIdPathToEiPropPath($guiIdPath);

// 		$fieldErrorInfo = new FieldErrorInfo();
		
		$translationDisplayable = new TranslationDisplayable($label, $this->n2nLocaleDefs);
		
		$translationMag = null;
		$eiFieldWrappers = array();
		
		$mandatory = false;
		foreach ($this->eiuEntryGuiAssemblers as $n2nLocaleId => $guiFieldAssembler) {
			$result = $guiFieldAssembler->assembleGuiField($guiIdPath);
			if ($result === null) continue;
			
			$eiuEntry = $guiFieldAssembler->getEiuEntryGui()->getEiuEntry();
			$fieldErrorInfo = $eiuEntry->getEiEntry()->getMappingErrorInfo()
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
				$translationMag = new TranslationMag($label, $this->getMarkClassKey());
			}
			
			if (null !== ($magAssembly = $result->getMagAssembly())) {
				$translationMag->putMagPropertyPath($n2nLocaleId, $magAssembly->getMagPropertyPath(), $fieldErrorInfo, $eiuEntry);
				if (!$mandatory) $mandatory = $magAssembly->isMandatory();
			} else {
				$translationMag->putDisplayable($n2nLocaleId, $result->getDisplayable(), $fieldErrorInfo);
			}
		}
		
		if ($translationDisplayable->isEmpty()) {
			return null;
		}
		
		$eiFieldWrapperWrapper = new EiFieldWrapperWrapper($eiFieldWrappers);
		
		if ($translationMag === null) {
			return new GuiFieldAssembly($translationDisplayable, $eiFieldWrapperWrapper);
		}
		
		$translationMag->setSrcLoadConfig($this->buildSrcLoadConfig($guiIdPath));
		
		$this->setupTranslationForm();
				
		$magInfo = $this->translationForm->registerMag($guiIdPath->__toString(), $translationMag);
		return new GuiFieldAssembly($translationDisplayable, $eiFieldWrapperWrapper, 
				new MagAssembly($mandatory, $magInfo['propertyPath'], $magInfo['magWrapper']));
	}
		
	public function isReadOnly(): bool {
		return $this->translationForm === null;
	}
	
	public function assembleGuiFieldFork(): ?GuiFieldForkEditable {
		if ($this->translationForm === null) {
			return null;
		}
		
		foreach ($this->eiuEntryGuiAssemblers as $eiuEntryGuiAssembler) {
			$eiuEntryGuiAssembler->finalize();
		}
		
		return $this;
	}
	
	public function isForkMandatory(): bool {
		return $this->min > 0;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiFieldFork::buildForkMag()
	 */
	public function getForkMag(): Mag {
		IllegalStateException::assertTrue($this->translationForm !== null);
		
		return new ForkMag($this->label, $this->translationForm, $this->n2nLocaleDefs, $this->min,
				$this->getMarkClassKey());
	}
	
	public function getInheritForkMagAssemblies(): array {
		$magAssemblies = array();
		foreach ($this->eiuEntryGuiAssemblers as $guiFieldAssembler) {
			$forkedMagAssemblies = $guiFieldAssembler->getEiuEntryGui()->getForkMagAssemblies();
			if (!empty($forkedMagAssemblies)) {
				array_push($magAssemblies, ...$forkedMagAssemblies);
			}
		}
		return $magAssemblies;
	}
	
	public function save() {
		if ($this->translationForm === null) return;
		
		$targetRelationEntries = array();
		foreach ($this->translationForm->getDispatchables() as $n2nLocaleId => $dispatchable) {
			$this->eiuEntryGuiAssemblers[$n2nLocaleId]->getEiuEntryGui()->save();
			$targetRelationEntries[$n2nLocaleId] = $this->targetRelationEntries[$n2nLocaleId];
			$targetRelationEntries[$n2nLocaleId]->getEiObject()->getLiveObject()
					->setN2nLocale(new N2nLocale($n2nLocaleId));
		}
		
		$this->toManyEiField->setValue($targetRelationEntries);
	}
	
	public function setCopyUrl(Url $copyUrl) {
		$this->srcUrl = $copyUrl;
	}
}

class EmptyDisplayable implements Displayable {
	private $displayable;
	
	public function __construct(Displayable $displayable) {
		$this->displayable = $displayable;
	}
	
	public function getDisplayItemType(): ?string {
		return $this->displayable->getDisplayItemType();
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

class SrcLoadConfig {
	private $guiIdPath;
	private $loadUrls;
	private $copyUrls;
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @param Url[] $url
	 */
	public function __construct(GuiIdPath $guiIdPath, array $loadUrls, array $copyUrls) {
		$this->guiIdPath = $guiIdPath;
		$this->loadUrls = $loadUrls;
		$this->copyUrls = $copyUrls;
	}
	
	/**
	 * @return string[]
	 */
	public function toAttrs() {
		$loadUrlDefs = [];
		foreach ($this->loadUrls as $n2nLocaleId => $url) {
			$loadUrlDefs[$n2nLocaleId] = array(
					'label' => N2nLocale::create($n2nLocaleId)->toPrettyId(),
					'url' => (string) $url,
					'n2nLocaleId' => $n2nLocaleId);
		}
		$copyUrlDefs = [];
		foreach ($this->copyUrls as $n2nLocaleId => $url) {
			$copyUrlDefs[$n2nLocaleId] = array(
					'label' => N2nLocale::create($n2nLocaleId)->toPrettyId(),
					'url' => (string) $url,
					'n2nLocaleId' => $n2nLocaleId);
		}
		return array('loadUrlDefs' => $loadUrlDefs, 'copyUrlDefs' => $copyUrlDefs, 'guiIdPath' => (string) $this->guiIdPath);
	}
}
