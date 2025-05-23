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

use rocket\op\ei\manage\DefPropPath;
use rocket\ui\si\content\impl\SiFields;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\ui\gui\field\GuiField;
use rocket\ui\si\content\impl\split\SplitStyle;
use rocket\ui\si\control\SiIconType;
use rocket\op\ei\manage\gui\EiSiMaskId;
use rocket\ui\gui\ViewMode;

class SplitGuiFieldFactory {
	private $lted;
	
	function __construct(LazyTranslationEssentialsDeterminer $lted, private bool $readOnly) {
		$this->lted = $lted;
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
	
	
	private function createReadOnlyGuiField(): ReadOnlyGuiField {
		$siField = SiFields::splitOutContext($this->lted->getTargetSiDeclaration(), $this->lted->getTargetEiuFrame()->createSiFrame())
				->setStyle(new SplitStyle(SiIconType::ICON_LANGUAGE, $this->lted->getViewMenuTooltip()));
		
		foreach ($this->lted->getN2nLocales() as $n2nLocale) {
			$n2nLocaleId = $n2nLocale->getId();
			$label = $n2nLocale->getName($this->lted->getDisplayN2nLocale());
			
			if ($this->lted->isN2nLocaleIdActive($n2nLocaleId)) {
				$siField->putValueBoundary($n2nLocaleId, $label,
								$this->lted->getTargetGuiValueBoundary($n2nLocaleId)->getSiValueBoundary())
						->setShortLabel($n2nLocale->toPrettyId());
			} else {
				$siField->putUnavailable($n2nLocaleId, $label)->setShortLabel($n2nLocale->toPrettyId());
			}
		}
		
		return new ReadOnlyGuiField($siField, $this->buildPlaceholderGuiFieldMap(new DefPropPath([])));
	}
	
	private function createEditableGuiField(): EditableGuiField {
		$siField = SiFields::splitInContext($this->lted->getTargetSiDeclaration(), $this->lted->getTargetEiuFrame()->createSiFrame())
				->setStyle(new SplitStyle(null, $this->lted->getViewMenuTooltip()))
				->setManagerStyle(new SplitStyle(SiIconType::ICON_GLOBE_AMERICAS, $this->lted->getManagerTooltip()))
				->setMin($this->lted->getMinNum())
				->setActiveKeys($this->lted->getActiveN2nLocaleIds())
				->setMandatoryKeys($this->lted->getMandatoryN2nLocaleIds());
		$targetEiuGuiDefinition = $this->lted->getTargetEiuGuiDefinition();
		$apiUrl = $this->lted->getTargetEiuFrame()->getApiUrl();
		
//		$propIds = array_map(
//				function ($defPropPath) { return (string) $defPropPath; },
//				$targetEiuGuiMaskDeclaration->getDefPropPaths());
		
		foreach ($this->lted->getN2nLocales() as $n2nLocale) {
			$n2nLocaleId = $n2nLocale->getId();
			$label = $n2nLocale->getName($this->lted->getDisplayN2nLocale());
			
			$pid = null;
			if (null !== ($activeTargetEiuEntry = $this->lted->getActiveTargetEiuEntry($n2nLocaleId))) {
				if ($activeTargetEiuEntry->isNew() || $activeTargetEiuEntry->isUnsaved()) {
					$siField->putEntry($n2nLocaleId, $label, $this->lted->getTargetGuiValueBoundary($n2nLocaleId)
									->createSiValueBoundary())
							->setShortLabel($n2nLocale->toPrettyId());
					continue;
				}
				
				$pid = $activeTargetEiuEntry->getPid();
			}

			$eiSiMaskId = new EiSiMaskId($targetEiuGuiDefinition->getEiGuiDefinition()->getEiTypePath(),
					ViewMode::determine($targetEiuGuiDefinition->isBulky(), false, $pid === null));

			$siField->putLazy($n2nLocaleId, $label, $apiUrl, (string) $eiSiMaskId, $pid, /* $targetEiuGuiDefinition->isBulky(), false,*/
							function () use ($n2nLocaleId) {
								return $this->lted->getTargetGuiValueBoundary($n2nLocaleId)->getSiValueBoundary();
							})
					->setShortLabel($n2nLocale->toPrettyId())
					/*->setPropIds($propIds)*/;
		}
		
// 		$guiFieldMap = new GuiFieldMap();
// 		foreach ($this->targetEiuGuiMaskDeclaration->getEiPropPaths() as $eiPropPath) {
// 			$guiFieldMap->putGuiField($eiPropPath, $this->createPlaceholderGuiField(new DefPropPath([$eiPropPath])));
// 		}
		
		return new EditableGuiField($this->lted, $siField, 
				$this->buildPlaceholderGuiFieldMap(new DefPropPath([])));
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return PlaceholderGuiField
	 */
	private function createPlaceholderGuiField(DefPropPath $defPropPath): PlaceholderGuiField {
		$siField = SiFields::splitPlaceholder($defPropPath);
		
		$placeholderGuiField = new PlaceholderGuiField($siField);
		
		if (!$this->readOnly) {
			$siField->setCopyStyle(new SplitStyle(null, $this->lted->getCopyTooltip()));
		}
		
// 		if (!$this->readOnly) {
			
// 			foreach ($this->lted->getN2nLocales() as $n2nLocale) {
// 				$n2nLocaleId = $n2nLocale->getId();
				
// 				$siField->putInputHandler($n2nLocaleId, new TranslationSiLazyInputHandler($this->lted, $n2nLocale, $defPropPath));
// 			}
// 		}
		
		$placeholderGuiField->setForkGuiFieldMap($this->buildPlaceholderGuiFieldMap($defPropPath));
		
		return $placeholderGuiField;
	}
	
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return GuiFieldMap|null
	 */
	private function buildPlaceholderGuiFieldMap($forkDefPropPath) {
		$eiPropPaths = [];
		if ($forkDefPropPath->isEmpty()) {
			$eiPropPaths = $this->lted->getTargetEiuGuiDefinition()->getEiPropPaths();
		} else {
			$eiPropPaths = $this->lted->getTargetEiuGuiDefinition()->getForkedEiPropPaths($forkDefPropPath);
		}
		
		if (empty($eiPropPaths)) {
			return null;
		}
		
		$guiFieldMap = new GuiFieldMap();
		foreach ($eiPropPaths as $eiPropPath) {
			$guiFieldMap->putGuiField($eiPropPath->toGuiFieldKey(),
					$this->createPlaceholderGuiField($forkDefPropPath->ext($eiPropPath)));
		}
		return $guiFieldMap;
	}
	
//	function getForkedDefPropPaths() {
//		return $this->targetEiuGuiMaskDeclaration->getForkedDefPropPaths();
//	}
	
// 	/**
// 	 * @param DefPropPath $defPropPath
// 	 * @return \rocket\impl\ei\component\prop\translation\gui\EditableGuiField
// 	 */
// 	private function createReadOnlyGuiField($defPropPath) {
// 		$siField = SiFields::splitOut();
// 		$readOnlyGuiField = new ReadOnlyGuiField($siField);

// 		foreach ($this->lted->getN2nLocales() as $n2nLocaleId => $n2nLocale) {
// 			$targetEiuGuiEntry = $this->lted->getActiveTargetEiuGuiEntry($n2nLocaleId);
			
// 			if ($targetEiuGuiEntry === null) {
// 				$siField->putUnavailable($n2nLocaleId, $n2nLocale->toPrettyId());
// 				continue;
// 			}
			
// 			$siField->putField($n2nLocaleId, $n2nLocale->toPrettyId(), (string) $defPropPath);
// 		}
		
// 		$forkedEiPropPaths = $this->lted->getTargetEiuGuiMaskDeclaration()->getForkedEiPropPaths($defPropPath);
		
// 		if (empty($forkedEiPropPaths)) {
// 			return $readOnlyGuiField;
// 		}
		
// 		$readOnlyGuiField->setForkedGuiFieldMap($this->createReadOnlyForkGuiFieldMap($defPropPath));
		
// 		return $readOnlyGuiField;
// 	}
	
//	/**
//	 * @param DefPropPath $defPropPath
//	 * @return \rocket\op\ei\manage\gui\GuiFieldMap
//	 */
//	private function createReadOnlyForkGuiFieldMap($defPropPath) {
//		$guiFieldMap = new GuiFieldMap();
//		foreach ($this->targetEiuGuiMaskDeclaration->getForkedEiPropPaths($defPropPath) as $forkedEiPropPath) {
//			$guiFieldMap->putGuiField($forkedEiPropPath,
//					$this->createReadOnlyGuiField($defPropPath->ext($forkedEiPropPath)));
//		}
//		return $guiFieldMap;
//	}
}


// class TranslationSiLazyInputHandler implements SiLazyInputHandler {
// 	private $lted;
// 	private $n2nLocale;
// 	private $defPropPath;
	
// 	function __construct(LazyTranslationEssentialsDeterminer $lted, N2nLocale $n2nLocale, DefPropPath $defPropPath) {
// 		$this->lted = $lted;
// 		$this->n2nLocale = $n2nLocale;
// 		$this->defPropPath = $defPropPath;
// 	}
	
// 	/**
// 	 * @return GuiField
// 	 */
// 	private function getGuiField(string $key) {
// 		return $this->lted->getTargetEiuGuiEntry($key)->getGuiFieldByDefPropPath($this->defPropPath);
// 	}
	
// 	function handlInput(array $data, array $uploadDefinitions) {
// 		$siField = $this->getGuiField()->getSiField();
		
// 		if ($siField === null || $siField->isReadOnly()) {
// 			throw new IllegalStateException('SiField of ' . $this->defPropPath . ' / ' . $this->n2nLocale 
// 					. ' not writable.');
// 		}
		
// 		$siField->handleInput($data);
// 	}
		
// 	function handleContextInput(string $key, array $data, array $uploadDefinitions) {
// 		$contextSiFields = $this->getGuiField()->getContextSiFields(); 
		
// 		if (isset($contextSiFields[$key]) || $contextSiFields[$key]->isReadOnly()) {
// 			throw new IllegalStateException('Context ' . $key . ' SiField ' . $this->defPropPath . ' / ' . $this->n2nLocale
// 					. 'not writable.');
// 		}
		
// 		$contextSiFields[$key]->handleInput($data);
// 	}
// }
