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
namespace rocket\impl\ei\component\prop\translation\gui;

use rocket\ei\manage\gui\GuiPropSetup;
use rocket\ei\manage\gui\GuiFieldAssembler;
use rocket\ei\util\gui\EiuGuiFrame;
use rocket\impl\ei\component\prop\translation\conf\TranslationConfig;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\gui\GuiFieldMap;
use rocket\ei\util\entry\EiuEntry;
use n2n\util\type\CastUtils;
use rocket\ei\util\gui\EiuEntryGui;
use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\si\content\impl\SiFields;

class TranslationGuiPropSetup implements GuiPropSetup, GuiFieldAssembler {
	private $targetEiuGuiFrame;
	private $translationConfig;
	
	function __construct(EiuGuiFrame $targetEiuGuiFrame, TranslationConfig $translationConfig) {
		$this->targetEiuGuiFrame = $targetEiuGuiFrame;
		$this->translationConfig = $translationConfig;
	}
	
	function getDisplayDefinition(): ?DisplayDefinition {
		return null;
	}
	
	function getGuiFieldAssembler(): GuiFieldAssembler {
		return $this;
	}
	
	function getForkedDisplayDefinition(GuiPropPath $guiPropPath): ?DisplayDefinition {
		return null;
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		$tef = new TranslationEssentialsFactory($eiu, $this->targetEiuGuiFrame, $this->translationConfig, $readOnly);
		
		$targetEiuEntries = $tef->getTargetEiuEntries();
		
		$guiFieldMap = new GuiFieldMap();
		foreach ($this->targetEiuGuiFrame->getEiPropPaths() as $eiPropPath) {
			$guiFieldMap->putGuiField($eiPropPath, $tef->createSplitGuiField($eiPropPath));
		}
		
		return new TranslationGuiField($this->translationConfig->getTranslationsMinNum(),
				$this->translationConfig->getN2nLocaleDefs(), $targetEiuEntries, $guiFieldMap);
		
		// 		if ($this->copyCommand !== null) {
		// 			$translationGuiField->setCopyUrl($targetEiuFrame->getUrlToCommand($this->copyCommand)
		// 					->extR(null, array('bulky' => $eiu->guiFrame()->isBulky())));
		// 		}
	}
	
}


class TranslationEssentialsFactory {
	private $eiu;
	private $targetEiuGuiFrame;
	private $translationConfig;
	private $readOnly;
	
	private $availableTargetEiuEntries = null;
	private $availableTargetEiuEntryGuis = null;
	
	function __construct(Eiu $eiu, EiuGuiFrame $targetEiuGuiFrame, TranslationConfig $translationConfig, bool $readOnly) {
		$this->eiu = $eiu;
		$this->targetEiuGuiFrame = $targetEiuGuiFrame;
		$this->translationConfig = $translationConfig;
		$this->readOnly = $readOnly;
	}
	
	/**
	 * @return EiuEntry[]
	 */
	private function ensureAvailableTargetEiuEntries() {
		if ($this->availableTargetEiuEntries !== null) {
			return;
		}
		
		$mappedValues = [];
		foreach ($this->eiu->field()->getValue() as $targetEiuEntry) {
			CastUtils::assertTrue($targetEiuEntry instanceof EiuEntry);
			
			$mappedValues[(string) $targetEiuEntry->getEntityObj()->getN2nLocale()] = $targetEiuEntry;
		}
		
		$this->availableTargetEiuEntries = [];
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			
			$this->availableTargetEiuEntries[$n2nLocaleId] = $mappedValues[$n2nLocaleId]
					?? null; // $this->createTargetEiuEntry($n2nLocaleDef);
		}
	}
	
	private function ensureAvailableTargetEiEntryGuis() {
		$this->ensureAvailableTargetEiuEntries();
		
		if ($this->availableTargetEiuEntryGuis !== null) {
			return;
		}
		
		$this->availableTargetEiuEntryGuis = [];
		foreach ($this->availableTargetEiuEntries as $n2nLocaleId => $targetEiuEntry) {
			$this->availableTargetEiuEntryGuis[$n2nLocaleId] = $this->targetEiuGuiFrame->newEntryGui($targetEiuEntry);
		}
	}
	
// 	/**
// 	 * @return EiuEntry[]
// 	 */
// 	function getTargetEiuEntries() {
// 		return $this->availableTargetEiuEntries;
// 	}
	
// 	/**
// 	 * @return EiuEntryGui[]
// 	 */
// 	function getTargetEiuEntryGuis() {
// 		return $this->availableTargetEiuEntryGuis;
// 	}
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return EiuEntry
	 */
	private function createTargetEiuEntry($n2nLocale) {
		$targetEiuEntry = $this->forkEiuFrame->newEntry();
		$targetEiuEntry->getEntityObj()->setN2nLocale($n2nLocale);
		return $targetEiuEntry;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return SplitGuiField
	 */
	function createSplitGuiField(EiPropPath $eiPropPath) {
		if ($this->readOnly) {
			return $this->createReadOnlyGuiField($eiPropPath);
		}
		
		$this->ensureAvailableTargetEiuEntries();
		
		
		
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			$label = $n2nLocaleDef->buildLabel($n2nLocale);
			
			$splitInGuiField->putGuiField($n2nLocaleId);
		}
		$siField->putLazy($n2nLocaleId, $label, $this->targetEiuGuiFrame->getEiuFrame()->getApiUrl(),
				$targetEiuEntryGui->entry()->getPid(),
				$this->targetEiuEntryGuis[$n2nLocaleId]->getGuiFieldByEiPropPath($eiPropPath)->getSiField(),
				$this->targetEiuGuiFrame->isBulky());
		
	}
	
	private function createReadOnlyGuiField(EiPropPath $eiPropPath) {
		$this->ensureAvailableTargetEiEntryGuis();
		
		$siField = SiFields::splitOut();
		$splitGuiField = new SplitGuiField($siField);
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			$label = $n2nLocaleDef->buildLabel($n2nLocale);
			
			if (!isset($this->availableTargetEiuEntryGuis[$n2nLocaleId])) {
				$siField->putUnavailable($n2nLocaleId, $label);
				continue;
			}
			
			$targetEiuEntryGui = $this->availableTargetEiuEntryGuis[$n2nLocaleId];
			$siField->putField($n2nLocaleId, $label, 
					$this->targetEiuEntryGuis[$n2nLocaleId]->getGuiFieldByEiPropPath($eiPropPath)->getSiField());
		}
		return $splitGuiField;
	}
	
	private function createForkedGuiFieldmap() {
		
	}
}

