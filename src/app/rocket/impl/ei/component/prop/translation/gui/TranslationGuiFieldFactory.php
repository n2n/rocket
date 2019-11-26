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

use rocket\ei\EiPropPath;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\si\content\impl\SiFields;
use rocket\ei\manage\gui\GuiFieldMap;
use n2n\l10n\N2nLocale;
use n2n\util\ex\IllegalStateException;

class TranslationGuiFieldFactory {
	private $led;
	
	function __construct(LazyTranslationEssentialsDeterminer $led) {
		$this->led = $led;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return SplitGuiField
	 */
	function createGuiField(EiPropPath $eiPropPath) {
		$guiPropPath = new GuiPropPath([$eiPropPath]);
		
		if ($this->readOnly) {
			return $this->createReadOnlyGuiField($guiPropPath);
		}
		
		return $this->createEditableGuiField($guiPropPath);
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\impl\ei\component\prop\translation\gui\SplitGuiField
	 */
	private function createEditableGuiField($guiPropPath) {
		$siField = SiFields::splitIn();
		$splitGuiField = new SplitGuiField($siField);
		
		$targetEiuGuiFrame = $this->lef->getTargetEiuGuiFrame();
		
		foreach ($this->lef->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			$n2nLocale = $n2nLocaleDef->getN2nLocale();
			$label = $n2nLocaleDef->buildLabel($n2nLocale);
			
			$pid = null;
			if (null !== ($availableTargetEiuEntry = $this->led->getAvailableTargetEiuEntry($n2nLocaleId))) {
				$pid = $availableTargetEiuEntry->entry()->getPid();
			}
			
			$siField->putLazy($n2nLocaleId, $label, $targetEiuGuiFrame->getEiuFrame()->getApiUrl(), $pid,
					(string) $guiPropPath, $targetEiuGuiFrame->isBulky(), 
					new TranslationSiLazyInputHandler($this->led, $n2nLocale, $guiPropPath));
		}
		
		$forkedEiPropPaths = $this->targetEiuGuiFrame->getForkedEiPropPaths($guiPropPath);
		
		if (empty($forkedEiPropPaths)) {
			return $splitGuiField;
		}
		
		$splitGuiField->setForkedGuiFieldMap($this->createEditableForkGuiFieldMap($guiPropPath));
		
		return $splitGuiField;
	}
	
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\ei\manage\gui\GuiFieldMap
	 */
	private function createEditableForkGuiFieldMap($guiPropPath) {
		$guiFieldMap = new GuiFieldMap();
		foreach ($this->targetEiuGuiFrame->getForkedEiPropPaths($guiPropPath) as $forkedEiPropPath) {
			$guiFieldMap->putGuiField($forkedEiPropPath,
					$this->createEditableGuiField($guiPropPath->ext($forkedEiPropPath)));
		}
		return $guiFieldMap;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\impl\ei\component\prop\translation\gui\SplitGuiField
	 */
	private function createReadOnlyGuiField($guiPropPath) {
		$siField = SiFields::splitOut();
		$readOnlyGuiField = new ReadOnlyGuiField($siField);
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			$label = $n2nLocaleDef->buildLabel($n2nLocale);
			
			if (!isset($this->availableTargetEiuEntryGuis[$n2nLocaleId])) {
				$siField->putUnavailable($n2nLocaleId, $label);
				continue;
			}
			
			$targetEiuEntryGui = $this->availableTargetEiuEntryGuis[$n2nLocaleId];
			$guiFieldWrapper = $targetEiuEntryGui->getGuiFieldWrapperByGuiPropPath($guiPropPath);
			$siField->putField($n2nLocaleId, $label, $guiFieldWrapper->getSiField(), $guiFieldWrapper->getContextSiFields());
		}
		
		$forkedEiPropPaths = $this->targetEiuGuiFrame->getForkedEiPropPaths($guiPropPath);
		
		if (empty($forkedEiPropPaths)) {
			return $readOnlyGuiField;
		}
		
		$readOnlyGuiField->setForkedGuiFieldMap($this->createReadOnlyForkGuiFieldMap($guiPropPath));
		
		return $readOnlyGuiField;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\ei\manage\gui\GuiFieldMap
	 */
	private function createReadOnlyForkGuiFieldMap($guiPropPath) {
		$guiFieldMap = new GuiFieldMap();
		foreach ($this->targetEiuGuiFrame->getForkedEiPropPaths($guiPropPath) as $forkedEiPropPath) {
			$guiFieldMap->putGuiField($forkedEiPropPath,
					$this->createReadOnlyGuiField($guiPropPath->ext($forkedEiPropPath)));
		}
		return $guiFieldMap;
	}
}


class TranslationSiLazyInputHandler implements SiLazyInputHandler {
	private $lted;
	private $n2nLocale;
	private $guiPropPath;
	
	function __construct(LazyTranslationEssentialsDeterminer $lted, N2nLocale $n2nLocale, GuiPropPath $guiPropPath) {
		$this->lted = $lted;
		$this->n2nLocale = $n2nLocale;
		$this->guiPropPath = $guiPropPath;
	}
	
	private function getGuiField() {
		return $this->ltef->getTargetEiuEntryGui($this->n2nLocale)->getGuiFieldByGuiPropPath($this->guiPropPath);
	}
	
	function handlInput(array $data, array $uploadDefinitions) {
		$siField = $this->getGuiField()->getSiField();
		
		if ($siField === null || $siField->isReadOnly()) {
			throw new IllegalStateException('SiField of ' . $this->guiPropPath . ' / ' . $this->n2nLocale 
					. 'not writable.');
		}
		
		$siField->handleInput($data);
	}
		
	function handleContextInput(string $key, array $data, array $uploadDefinitions) {
		$contextSiFields = $this->getGuiField()->getContextSiFields(); 
		
		if (isset($contextSiFields[$key]) || $contextSiFields[$key]->isReadOnly()) {
			throw new IllegalStateException('Context ' . $key . ' SiField ' . $this->guiPropPath . ' / ' . $this->n2nLocale
					. 'not writable.');
		}
		
		$contextSiFields[$key]->handleInput($data);
	}
}
