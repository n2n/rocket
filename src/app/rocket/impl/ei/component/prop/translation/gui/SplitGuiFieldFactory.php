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
use rocket\si\content\impl\split\SiLazyInputHandler;

class SplitGuiFieldFactory {
	private $lted;
	private $readOnly;
	
	function __construct(LazyTranslationEssentialsDeterminer $lted, bool $readOnly) {
		$this->lted = $lted;
		$this->readOnly = $readOnly;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return EditableGuiField
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
	 * @return \rocket\impl\ei\component\prop\translation\gui\EditableGuiField
	 */
	private function createEditableGuiField($guiPropPath) {
		$siField = SiFields::splitIn();
		$splitGuiField = new EditableGuiField($siField);
		
		$targetEiuGuiFrame = $this->lted->getTargetEiuGuiFrame();
		
		foreach ($this->lted->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			$n2nLocale = $n2nLocaleDef->getN2nLocale();
			$label = $n2nLocaleDef->buildLabel($n2nLocale);
			
			$pid = null;
			if (null !== ($availableTargetEiuEntry = $this->lted->getAvailableTargetEiuEntry($n2nLocaleId))) {
				$pid = $availableTargetEiuEntry->entry()->getPid();
			}
			
			$siField->putLazy($n2nLocaleId, $label, $targetEiuGuiFrame->getEiuFrame()->getApiUrl(), $pid,
					(string) $guiPropPath, $targetEiuGuiFrame->isBulky(), 
					new TranslationSiLazyInputHandler($this->lted, $n2nLocale, $guiPropPath));
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
	
	function getForkedGuiPropPaths() {
		return $this->targetEiuGuiFrame->getForkedGuiPropPaths();
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\impl\ei\component\prop\translation\gui\EditableGuiField
	 */
	private function createReadOnlyGuiField($guiPropPath) {
		$siField = SiFields::splitOut();
		$readOnlyGuiField = new ReadOnlyGuiField($siField);

		foreach ($this->lted->getN2nLocales() as $n2nLocaleId => $n2nLocale) {
			$targetEiuEntryGui = $this->lted->getActiveTargetEiuEntryGui($n2nLocaleId);
			
			if ($targetEiuEntryGui === null) {
				$siField->putUnavailable($n2nLocaleId, $n2nLocale->toPrettyId());
				continue;
			}
			
			$siField->putField($n2nLocaleId, $n2nLocale->toPrettyId(), $fieldId);
		}
		
		$forkedEiPropPaths = $this->lted->getTargetEiuGuiFrame()->getForkedEiPropPaths($guiPropPath);
		
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
