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

use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\si\content\impl\SiFields;
use rocket\ei\manage\gui\GuiFieldMap;
use n2n\l10n\N2nLocale;
use n2n\util\ex\IllegalStateException;
use rocket\si\content\impl\split\SiLazyInputHandler;
use rocket\ei\manage\gui\field\GuiField;
use rocket\si\content\impl\split\SplitStyle;
use rocket\si\control\SiIconType;

class SplitGuiFieldFactory {
	private $lted;
	private $readOnly;
	
	function __construct(LazyTranslationEssentialsDeterminer $lted, bool $readOnly) {
		$this->lted = $lted;
		$this->readOnly = $readOnly;
	}
	
	/**
	 * @return GuiField
	 */
	function createGuiField() {
		if ($this->readOnly) {
			return $this->createReadOnlyGuiField();
		}
		
		return $this->createEditableGuiField();
	}
	
	
	private function createReadOnlyGuiField() {
		$siField = SiFields::splitOutContext($this->lted->getTargetSiDeclaration())
				->setStyle(new SplitStyle(SiIconType::ICON_LANGUAGE, $this->lted->getViewMenuTooltip()));
		
		foreach ($this->lted->getN2nLocales() as $n2nLocale) {
			$n2nLocaleId = $n2nLocale->getId();
			$label = $n2nLocale->getName($this->lted->getDisplayN2nLocale());
			
			if (null !== ($activeTargetEiuEntryGui = $this->lted->getActiveTargetEiuEntryGui($n2nLocaleId))) {
				$siField->putEntry($n2nLocaleId, $label, $activeTargetEiuEntryGui->createSiEntry())
						->setShortLabel($n2nLocale->toPrettyId());
			} else {
				$siField->putUnavailable($n2nLocaleId, $label)->setShortLabel($n2nLocale->toPrettyId());
			}
		}
		
		return new ReadOnlyGuiField($siField, $this->buildPlaceholderGuiFieldMap(new GuiPropPath([])));
	}
	
	private function createEditableGuiField() {
		$siField = SiFields::splitInContext($this->lted->getTargetSiDeclaration())
				->setStyle(new SplitStyle(SiIconType::ICON_LANGUAGE, $this->lted->getViewMenuTooltip()))
				->setMin($this->lted->getMinNum())
				->setActiveKeys($this->lted->getActiveN2nLocaleIds())
				->setMandatoryKeys($this->lted->getMandatoryN2nLocaleIds());
		$targetEiuGuiFrame = $this->lted->getTargetEiuGuiFrame();
		$apiUrl = $targetEiuGuiFrame->getEiuFrame()->getApiUrl();
		
		$fieldIds = array_map(
				function ($guiPropPath) { return (string) $guiPropPath; }, 
				$targetEiuGuiFrame->getGuiPropPaths());
		
		foreach ($this->lted->getN2nLocales() as $n2nLocale) {
			$n2nLocaleId = $n2nLocale->getId();
			$label = $n2nLocale->getName($this->lted->getDisplayN2nLocale());
			
			$pid = null;
			if (null !== ($activeTargetEiuEntry = $this->lted->getActiveTargetEiuEntry($n2nLocaleId))) {
				$pid = $activeTargetEiuEntry->getPid();
			}
			
			$siField->putLazy($n2nLocaleId, $label, $apiUrl, $pid, $targetEiuGuiFrame->isBulky(), false)
					->setShortLabel($n2nLocale->toPrettyId())
					->setFieldIds($fieldIds);
		}
		
// 		$guiFieldMap = new GuiFieldMap();
// 		foreach ($this->targetEiuGuiFrame->getEiPropPaths() as $eiPropPath) {
// 			$guiFieldMap->putGuiField($eiPropPath, $this->createPlaceholderGuiField(new GuiPropPath([$eiPropPath])));
// 		}
		
		return new EditableGuiField($this->lted, $siField, 
				$this->buildPlaceholderGuiFieldMap(new GuiPropPath([])));
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\impl\ei\component\prop\translation\gui\EditableGuiField
	 */
	private function createPlaceholderGuiField($guiPropPath) {
		$siField = SiFields::splitPlaceholder((string) $this->lted->getGuiPropPath(), $guiPropPath);
		
		$placeholderGuiField = new PlaceholderGuiField($siField);
		
		if (!$this->readOnly) {
			
			foreach ($this->lted->getN2nLocales() as $n2nLocale) {
				$n2nLocaleId = $n2nLocale->getId();
				
				$siField->putInputHandler($n2nLocaleId, new TranslationSiLazyInputHandler($this->lted, $n2nLocale, $guiPropPath));
			}
		}
		
		$placeholderGuiField->setForkGuiFieldMap($this->buildPlaceholderGuiFieldMap($guiPropPath));
		
		return $placeholderGuiField;
	}
	
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\ei\manage\gui\GuiFieldMap|null
	 */
	private function buildPlaceholderGuiFieldMap($forkGuiPropPath) {
		$eiPropPaths = [];
		if ($forkGuiPropPath->isEmpty()) {
			$eiPropPaths = $this->lted->getTargetEiuGuiFrame()->getEiPropPaths();
		} else {
			$eiPropPaths = $this->lted->getTargetEiuGuiFrame()->getForkedEiPropPaths($forkGuiPropPath);
		}
		
		if (empty($eiPropPaths)) {
			return null;
		}
		
		$guiFieldMap = new GuiFieldMap();
		foreach ($eiPropPaths as $eiPropPath) {
			$guiFieldMap->putGuiField($eiPropPath,
					$this->createPlaceholderGuiField($forkGuiPropPath->ext($eiPropPath)));
		}
		return $guiFieldMap;
	}
	
	function getForkedGuiPropPaths() {
		return $this->targetEiuGuiFrame->getForkedGuiPropPaths();
	}
	
// 	/**
// 	 * @param GuiPropPath $guiPropPath
// 	 * @return \rocket\impl\ei\component\prop\translation\gui\EditableGuiField
// 	 */
// 	private function createReadOnlyGuiField($guiPropPath) {
// 		$siField = SiFields::splitOut();
// 		$readOnlyGuiField = new ReadOnlyGuiField($siField);

// 		foreach ($this->lted->getN2nLocales() as $n2nLocaleId => $n2nLocale) {
// 			$targetEiuEntryGui = $this->lted->getActiveTargetEiuEntryGui($n2nLocaleId);
			
// 			if ($targetEiuEntryGui === null) {
// 				$siField->putUnavailable($n2nLocaleId, $n2nLocale->toPrettyId());
// 				continue;
// 			}
			
// 			$siField->putField($n2nLocaleId, $n2nLocale->toPrettyId(), (string) $guiPropPath);
// 		}
		
// 		$forkedEiPropPaths = $this->lted->getTargetEiuGuiFrame()->getForkedEiPropPaths($guiPropPath);
		
// 		if (empty($forkedEiPropPaths)) {
// 			return $readOnlyGuiField;
// 		}
		
// 		$readOnlyGuiField->setForkedGuiFieldMap($this->createReadOnlyForkGuiFieldMap($guiPropPath));
		
// 		return $readOnlyGuiField;
// 	}
	
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
	
	/**
	 * @return GuiField
	 */
	private function getGuiField(string $key) {
		return $this->ltef->getTargetEiuEntryGui($key)->getGuiFieldByGuiPropPath($this->guiPropPath);
	}
	
	function handlInput(array $data, array $uploadDefinitions) {
		$siField = $this->getGuiField()->getSiField();
		
		if ($siField === null || $siField->isReadOnly()) {
			throw new IllegalStateException('SiField of ' . $this->guiPropPath . ' / ' . $this->n2nLocale 
					. ' not writable.');
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
