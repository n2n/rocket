<?php
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\op\ei\component;

// use n2n\util\type\ArgUtils;
// use rocket\op\ei\manage\gui\EiGuiDefinition;
// use rocket\op\ei\manage\gui\EiGuiValueBoundaryAssembler;
// use rocket\op\ei\manage\gui\EiGuiValueBoundary;
// 
// use rocket\op\ei\EiPropPath;
// use rocket\op\ei\manage\gui\GuiPropFork;
// use rocket\op\ei\manage\gui\GuiProp;
// use rocket\op\ei\util\entry\EiuEntry;
// use rocket\op\ei\mask\EiMask;
// use rocket\op\ei\manage\DefPropPath;
// use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
// use n2n\impl\web\ui\view\html\HtmlView;
// use rocket\op\ei\manage\entry\EiEntry;
// use rocket\op\ei\util\Eiu;
// use rocket\op\ei\mask\model\ControlOrder;
// use rocket\op\ei\manage\EiObject;
// use n2n\l10n\N2nLocale;
// use n2n\core\container\N2nContext;
// use rocket\op\ei\component\prop\GuiEiPropFork;
// use rocket\op\ei\manage\idname\SummarizedStringBuilder;

// class ControlFactory {
// 	private $eiMask;
	
// 	public function __construct(EiMask $eiMask) {
// 		$this->eiMask = $eiMask;
// 	}
	
// 	/**
// 	 * @param N2nContext $n2nContext
// 	 * @param EiGuiDefinition|null $guiDefinition
// 	 * @return \rocket\op\ei\manage\gui\EiGuiDefinition
// 	 */
// 	public function createEntryGuiControlDefinition(N2nContext $n2nContext, &$guiDefinition = null) {
// 		$eiu = new Eiu($n2nContext, $this->eiMask);
		
// 		$guiDefinition = new EiGuiDefinition($this->eiMask->getLabelLstr());
// 		$guiDefinition->setIdentityStringPattern($this->eiMask->getIdentityStringPattern());
		
// 		foreach ($this->eiMask->getEiPropCollection() as $eiPropPathStr => $eiProp) {
// 			$eiPropPath = EiPropPath::create($eiPropPathStr);
			
// 			if (($eiProp instanceof GuiEiProp) && null !== ($guiProp = $eiProp->buildGuiProp($eiu))){
// 				ArgUtils::valTypeReturn($guiProp, GuiProp::class, $eiProp, 'buildGuiProp');
				
// 				$guiDefinition->putGuiProp($eiPropPath, $guiProp, EiPropPath::from($eiProp));
// 			}
			
// 			if (($eiProp instanceof GuiEiPropFork) && null !== ($guiPropFork = $eiProp->buildGuiPropFork($eiu))){
// 				ArgUtils::valTypeReturn($guiPropFork, GuiPropFork::class, $eiProp, 'buildGuiPropFork');
				
// 				$guiDefinition->putGuiPropFork($eiPropPath, $guiPropFork);
// 			}
// 		}
		
// 		foreach ($this->eiMask->getEiModCollection() as $eiModificator) {
// 			$eiModificator->setupEiGuiDefinition($eiu);
// 		}
		
// 		return $guiDefinition;
// 	}
	
// 	/**
// 	 * @param EiObject $eiObject
// 	 * @param N2nLocale $n2nLocale
// 	 * @return string
// 	 */
// 	private function createDefaultIdentityString(EiObject $eiObject, N2nLocale $n2nLocale, EiGuiDefinition $guiDefinition) {
// 		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
// 		$idPatternPart = null;
// 		$namePatternPart = null;
		
// 		foreach ($guiDefinition->getStringRepresentableGuiProps() as $eiPropPathStr => $guiProp) {
// 			if ($eiPropPathStr == $this->eiMask->getEiType()->getEntityModel()->getIdDef()->getPropertyName()) {
// 				$idPatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
// 			} else {
// 				$namePatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
// 			}
			
// 			if ($namePatternPart !== null) break;
// 		}
		
// 		if ($idPatternPart === null) {
// 			$idPatternPart = $eiObject->getEiEntityObj()->hasId() ?
// 			$this->eiMask->getEiType()->idToPid($eiObject->getEiEntityObj()->getId()) : 'new';
// 		}
		
// 		if ($namePatternPart === null) {
// 			$namePatternPart = $this->eiMask->getLabelLstr()->t($n2nLocale);
// 		}
		
// 		return $guiDefinition->createIdentityString($namePatternPart . ' #' . $idPatternPart, $eiObject, $n2nLocale);
// 	}
	
// 	/**
// 	 * @param EiObject $eiObject
// 	 * @param N2nLocale $n2nLocale
// 	 * @return string
// 	 */
// 	public function createIdentityString(EiObject $eiObject, N2nLocale $n2nLocale, EiGuiDefinition $guiDefinition) {
// 		$identityStringPattern = $this->eiMaskDef->getIdentityStringPattern();
		
// 		if ($manageState === null || $identityStringPattern === null) {
// 			return $this->createDefaultIdentityString($eiObject, $n2nLocale, $guiDefinition);
// 		}
		
// 		return $guiDefinition->createIdentityString($identityStringPattern, $eiObject, $n2nLocale);
// 	}
	
// // 	/**
// // 	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
// // 	 * @param HtmlView $view
// // 	 * @return Control[]
// // 	 */
// // 	public function createOverallControls(EiGuiMaskDeclaration $eiGuiMaskDeclaration, HtmlView $view) {
// // 		$eiu = new Eiu($eiGuiMaskDeclaration);
		
// // 		$controls = array();
		
// // 		foreach ($this->eiMask->getEiCommandCollection() as $eiCmdId => $eiCmd) {
// // 			if (!($eiCmd instanceof OverallControlComponent) || !$eiu->frame()->isExecutableBy($eiCmd)) {
// // 				continue;
// // 			}
			
// // 			$overallControls = $eiCmd->createOverallControls($eiu, $view);
// // 			ArgUtils::valArrayReturn($overallControls, $eiCmd, 'createOverallControls', Control::class);
// // 			foreach ($overallControls as $controlId => $control) {
// // 				$controls[ControlOrder::buildControlId($eiCmdId, $controlId)] = $control;
// // 			}
// // 		}
		
// // 		return $this->eiMask->getDisplayScheme()->getOverallControlOrder()->sort($controls);
// // 	}
	
// 	/**
// 	 * @param EiGuiValueBoundary $eiGuiValueBoundary
// 	 * @param HtmlView $view
// 	 * @return Control[]
// 	 */
// 	public function createEntryGuiControls(EiGuiValueBoundary $eiGuiValueBoundary, HtmlView $view) {
// 		$eiu = new Eiu($eiGuiValueBoundary);
		
// 		$controls = array();
		
// 		foreach ($this->eiMask->getEiCommandCollection() as $eiCmdId => $eiCmd) {
// 			if (!($eiCmd instanceof EntryGuiControlComponent)
// 					|| !$eiu->entry()->access()->isExecutableBy($eiCmd)) {
// 				continue;
// 			}
			
// 			$entryControls = $eiCmd->createEntryGuiControls($eiu, $view);
// 			ArgUtils::valArrayReturn($entryControls, $eiCmd, 'createEntryGuiControls', Control::class);
// 			foreach ($entryControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCmdId, $controlId)] = $control;
// 			}
// 		}
		
// 		return $this->eiMask->getDisplayScheme()->getEntryGuiControlOrder()->sort($controls);
// 	}
	
// 	/**
// 	 * @param EiMask $eiMask
// 	 * @param EiuEntry $eiuEntry
// 	 * @param int $viewMode
// 	 * @param array $eiPropPaths
// 	 * @return EiGuiValueBoundary
// 	 */
// 	public static function createEiGuiValueBoundary(EiGuiMaskDeclaration $eiGuiMaskDeclaration, EiEntry $eiEntry, array $defPropPaths, int $treeLevel = null) {
// 		ArgUtils::valArrayLike($defPropPaths, DefPropPath::class);
		
// 		$eiGuiValueBoundary = new EiGuiValueBoundary($eiGuiMaskDeclaration, $eiEntry, $treeLevel);
		
// 		$eiGuiField = new EiGuiValueBoundaryAssembler($eiGuiValueBoundary);
		
// 		foreach ($defPropPaths as $defPropPath) {
// 			$eiGuiField->assembleGuiField($defPropPath);
// 		}
		
// 		$eiGuiField->finalize();
		
// 		return $eiGuiValueBoundary;
// 	}
// }


// // class ModEiGuiListener implements EiGuiListener {
// // 	private $eiModificatorCollection;

// // 	public function __construct(EiModCollection $eiModificatorCollection) {
// // 		$this->eiModificatorCollection = $eiModificatorCollection;
// // 	}

// // 	public function onInitialized(EiGuiMaskDeclaration $eiGuiMaskDeclaration) {
// // 		foreach ($this->eiModificatorCollection as $eiModificator) {
// // 			$eiModificator->onEiGuiMaskDeclarationInitialized($eiGuiMaskDeclaration);
// // 		}
// // 	}

// // 	public function onNewEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary) {
// // 		foreach ($this->eiModificatorCollection as $eiModificator) {
// // 			$eiModificator->onNewEiGuiValueBoundary($eiGuiValueBoundary);
// // 		}
// // 	}

// // 	public function onNewView(HtmlView $view) {
// // 		foreach ($this->eiModificatorCollection as $eiModificator) {
// // 			$eiModificator->onNewView($view);
// // 		}
// // 	}

// // }