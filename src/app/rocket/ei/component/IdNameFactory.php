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
namespace rocket\ei\component;

use n2n\util\type\ArgUtils;
use rocket\ei\EiPropPath;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\mask\EiMask;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\util\Eiu;
use rocket\ei\mask\model\ControlOrder;
use rocket\ei\manage\EiObject;
use n2n\l10n\N2nLocale;
use n2n\core\container\N2nContext;
use rocket\ei\manage\gui\IdNameDefinition;

class IdNameFactory {
	private $eiMask;
	
	public function __construct(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param IdNameDefinition|null $IdNameDefinition
	 * @return IdNameDefinition
	 */
	public function createIdNameDefinition(N2nContext $n2nContext, &$IdNameDefinition = null) {
		$eiu = new Eiu($n2nContext, $this->eiMask);
		
		$IdNameDefinition = new IdNameDefinition($this->eiMask->getLabelLstr());
		$IdNameDefinition->setIdentityStringPattern($this->eiMask->getIdentityStringPattern());
		
		foreach ($this->eiMask->getEiPropCollection() as $eiPropPathStr => $eiProp) {
			$eiPropPath = EiPropPath::create($eiPropPathStr);
			
			if (($eiProp instanceof IdNameEiProp) && null !== ($IdNameProp = $eiProp->buildIdNameProp($eiu))) {
				ArgUtils::valTypeReturn($IdNameProp, IdNameProp::class, $eiProp, 'buildIdNameProp');
			
				$IdNameDefinition->putIdNameProp($eiPropPath, $IdNameProp, EiPropPath::from($eiProp));
			}
			
			if (($eiProp instanceof IdNameEiPropFork) && null !== ($IdNamePropFork = $eiProp->buildIdNamePropFork($eiu))){
				ArgUtils::valTypeReturn($IdNamePropFork, IdNamePropFork::class, $eiProp, 'buildIdNamePropFork');
				
				$IdNameDefinition->putIdNamePropFork($eiPropPath, $IdNamePropFork);
			}
		}
		
		foreach ($this->eiMask->getEiCommandCollection() as $eiCommandPathStr => $eiCommand) {
			$eiCommandPath = $eiCommand->getWrapper()->getEiCommandPath();
			
			$IdNameDefinition->putIdNameCommand($eiCommandPath, $eiCommand);
		}
		
		foreach ($this->eiMask->getEiModificatorCollection() as $eiModificator) {
			$eiModificator->setupIdNameDefinition($eiu);
		}
		
		return $IdNameDefinition;
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	private function createDefaultIdentityString(EiObject $eiObject, N2nLocale $n2nLocale, IdNameDefinition $IdNameDefinition) {
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
		$idPatternPart = null;
		$namePatternPart = null;
		
		foreach ($IdNameDefinition->getStringRepresentableIdNameProps() as $eiPropPathStr => $IdNameProp) {
			if ($eiPropPathStr == $this->eiMask->getEiType()->getEntityModel()->getIdDef()->getPropertyName()) {
				$idPatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
			} else {
				$namePatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
			}
			
			if ($namePatternPart !== null) break;
		}
		
		if ($idPatternPart === null) {
			$idPatternPart = $eiObject->getEiEntityObj()->hasId() ?
					$this->eiMask->getEiType()->idToPid($eiObject->getEiEntityObj()->getId()) : 'new';
		}
		
		if ($namePatternPart === null) {
			$namePatternPart = $this->eiMask->getLabelLstr()->t($n2nLocale);
		}
		
		return $IdNameDefinition->createIdentityString($namePatternPart . ' #' . $idPatternPart, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiObject $eiObject, N2nLocale $n2nLocale, IdNameDefinition $IdNameDefinition) {
		$identityStringPattern = $this->eiMaskDef->getIdentityStringPattern();
		
		if ($manageState === null || $identityStringPattern === null) {
			return $this->createDefaultIdentityString($eiObject, $n2nLocale, $IdNameDefinition);
		}
		
		return $IdNameDefinition->createIdentityString($identityStringPattern, $eiObject, $n2nLocale);
	}

// 	/**
// 	 * @param EiIdName $eiIdName
// 	 * @param HtmlView $view
// 	 * @return Control[]
// 	 */
// 	public function createOverallControls(EiIdName $eiIdName, HtmlView $view) {
// 		$eiu = new Eiu($eiIdName);
		
// 		$controls = array();
		
// 		foreach ($this->eiMask->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof OverallControlComponent) || !$eiu->frame()->isExecutableBy($eiCommand)) {
// 				continue;
// 			}
					
// 			$overallControls = $eiCommand->createOverallControls($eiu, $view);
// 			ArgUtils::valArrayReturn($overallControls, $eiCommand, 'createOverallControls', Control::class);
// 			foreach ($overallControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
// 			}
// 		}
		
// 		return $this->eiMask->getDisplayScheme()->getOverallControlOrder()->sort($controls);
// 	}
	
	/**
	 * @param EiEntryIdName $eiEntryIdName
	 * @param HtmlView $view
	 * @return Control[]
	 */
	public function createEntryIdNameControls(EiEntryIdName $eiEntryIdName, HtmlView $view) {
		$eiu = new Eiu($eiEntryIdName);
		
		$controls = array();
		
		foreach ($this->eiMask->getEiCommandCollection() as $eiCommandId => $eiCommand) {
			if (!($eiCommand instanceof EntryIdNameControlComponent)
					|| !$eiu->entry()->access()->isExecutableBy($eiCommand)) {
				continue;
			}
			
			$entryControls = $eiCommand->createEntryIdNameControls($eiu, $view);
			ArgUtils::valArrayReturn($entryControls, $eiCommand, 'createEntryIdNameControls', Control::class);
			foreach ($entryControls as $controlId => $control) {
				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
			}
		}
		
		return $this->eiMask->getDisplayScheme()->getEntryIdNameControlOrder()->sort($controls);
	}
	
	/**
	 * @param EiMask $eiMask
	 * @param EiuEntry $eiuEntry
	 * @param int $viewMode
	 * @param array $eiPropPaths
	 * @return EiEntryIdName
	 */
	public static function createEiEntryIdName(EiIdName $eiIdName, EiEntry $eiEntry, array $IdNameFieldPaths, int $treeLevel = null) {
		ArgUtils::valArrayLike($IdNameFieldPaths, IdNameFieldPath::class);
		
		$eiEntryIdName = new EiEntryIdName($eiIdName, $eiEntry, $treeLevel);
		
		$IdNameFieldAssembler = new EiEntryIdNameAssembler($eiEntryIdName);
				
		foreach ($IdNameFieldPaths as $IdNamePropPath) {
			$IdNameFieldAssembler->assembleIdNameField($IdNamePropPath);
		}
		
		$IdNameFieldAssembler->finalize();
				
		return $eiEntryIdName;
	}
}


// class ModEiIdNameListener implements EiIdNameListener {
// 	private $eiModificatorCollection;
	
// 	public function __construct(EiModificatorCollection $eiModificatorCollection) {
// 		$this->eiModificatorCollection = $eiModificatorCollection;
// 	}
	
// 	public function onInitialized(EiIdName $eiIdName) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onEiIdNameInitialized($eiIdName);
// 		}
// 	}
	
// 	public function onNewEiEntryIdName(EiEntryIdName $eiEntryIdName) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onNewEiEntryIdName($eiEntryIdName);
// 		}
// 	}
	
// 	public function onNewView(HtmlView $view) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onNewView($view);
// 		}
// 	}

// }