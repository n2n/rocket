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
namespace rocket\op\ei\component;

use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\gui\GuiDefinition;
use rocket\op\ei\manage\gui\EiGuiValueBoundary;

use rocket\op\ei\EiPropPath;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\util\Eiu;

use n2n\core\container\N2nContext;
use rocket\op\ei\manage\gui\GuiFieldMap;
use rocket\op\ei\manage\gui\GuiException;
use rocket\op\ei\manage\gui\ViewMode;
use rocket\op\ei\manage\gui\field\GuiField;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\gui\EiGuiDeclaration;
use rocket\op\ei\manage\ManageState;
use n2n\util\type\CastUtils;
use rocket\op\ei\manage\gui\EiGuiEntry;
use rocket\op\ei\manage\gui\GuiBuildFailedException;
use rocket\op\ei\manage\gui\control\GuiControlPath;
use rocket\op\ei\manage\api\ApiController;
use rocket\op\ei\manage\api\ApiControlCallId;
use rocket\op\ei\manage\gui\control\GuiControlMap;

class GuiFactory {
//	private $eiMask;
	
	public function __construct(EiMask $eiMask) {
//		$this->eiMask = $eiMask;
	}

	
	
	
// 	/**
// 	 * @param EiFrame $eiFrame
// 	 * @param int $viewMode
// 	 * @throws \InvalidArgumentException
// 	 * @return \rocket\op\ei\manage\gui\EiGuiMaskDeclaration
// 	 */
// 	function createEiGuiMaskDeclaration(EiFrame $eiFrame, int $viewMode) {
// 		if (!$this->eiMask->getEiType()->isA($eiFrame->getContextEiEngine()->getEiMask()->getEiType())) {
// 			throw new \InvalidArgumentException('Incompatible EiGuiMaskDeclaration');
// 		}
		
// 		$eiGuiMaskDeclaration = new EiGuiMaskDeclaration($eiFrame, $this->eiMask, $viewMode);
		
// 		$this->eiMask->getEiModCollection()->setupEiGuiMaskDeclaration($eiGuiMaskDeclaration);
		
		
// // 		if (!$init) {
// // 			$this->noInitCb($eiGuiMaskDeclaration);
// // 			return $eiGuiMaskDeclaration;
// // 		}
		
// // 		foreach ($guiDefinition->getGuiDefinitionListeners() as $listener) {
// // 			$listener->onNewEiGuiMaskDeclaration($eiGuiMaskDeclaration);
// // 		}
		
// // 		if (!$eiGuiMaskDeclaration->isInit()) {
// // 			$this->eiMask->getDisplayScheme()->initEiGuiMaskDeclaration($eiGuiMaskDeclaration, $guiDefinition);
// // 		}
		
// 		return $eiGuiMaskDeclaration;
// 	}
	
// 	/**
// 	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
// 	 * @param HtmlView $view
// 	 * @return Control[]
// 	 */
// 	public function createOverallControls(EiGuiMaskDeclaration $eiGuiMaskDeclaration, HtmlView $view) {
// 		$eiu = new Eiu($eiGuiMaskDeclaration);
		
// 		$controls = array();
		
// 		foreach ($this->eiMask->getEiCommandCollection() as $eiCmdId => $eiCmd) {
// 			if (!($eiCmd instanceof OverallControlComponent) || !$eiu->frame()->isExecutableBy($eiCmd)) {
// 				continue;
// 			}
					
// 			$overallControls = $eiCmd->createOverallControls($eiu, $view);
// 			ArgUtils::valArrayReturn($overallControls, $eiCmd, 'createOverallControls', Control::class);
// 			foreach ($overallControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCmdId, $controlId)] = $control;
// 			}
// 		}
		
// 		return $this->eiMask->getDisplayScheme()->getOverallControlOrder()->sort($controls);
// 	}
	
// 	/**
// 	 * @param EiGuiValueBoundary $eiGuiValueBoundary
// 	 * @param HtmlView $view
// 	 * @return GuiControl[]
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

	public static function createEiGuiEntry(EiFrame $eiFrame, EiGuiMaskDeclaration $eiGuiMaskDeclaration,
			EiEntry $eiEntry, bool $entryGuiControlsIncluded): EiGuiEntry {

		$n2nLocale = $eiFrame->getN2nContext()->getN2nLocale();
		$idName = null;
		if (!$eiEntry->isNew()) {
			$deterIdNameDefinition = $eiEntry->getEiMask()->getEiEngine()->getIdNameDefinition();
			$idName = $deterIdNameDefinition->createIdentityString($eiEntry->getEiObject(),
					$eiFrame->getN2nContext(), $n2nLocale);
		}

		$eiGuiEntry = new EiGuiEntry($eiGuiMaskDeclaration, $eiEntry, $idName, $n2nLocale);
		
		$guiFieldMap = new GuiFieldMap();
		foreach ($eiGuiMaskDeclaration->getEiPropPaths() as $eiPropPath) {
			$guiField = self::buildGuiField($eiFrame, $eiGuiMaskDeclaration, $eiGuiEntry, $eiPropPath);
			
			if ($guiField !== null) {
				$guiFieldMap->putGuiField($eiPropPath, $guiField);	
			}
		}

		$guiControlMap = null;
		if ($entryGuiControlsIncluded) {
			$guiControlMap = $eiGuiMaskDeclaration->createEntryGuiControlsMap($eiFrame, $eiEntry);
		}

		$eiGuiEntry->init($guiFieldMap, $guiControlMap);
				
		return $eiGuiEntry;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 * @param EiGuiEntry $eiGuiEntry
	 * @param EiPropPath $eiPropPath
	 * @return GuiField|null
	 */
	private static function buildGuiField(EiFrame $eiFrame, EiGuiMaskDeclaration $eiGuiMaskDeclaration,
			EiGuiEntry $eiGuiEntry, EiPropPath $eiPropPath): ?GuiField {
		$readOnly = ViewMode::isReadOnly($eiGuiMaskDeclaration->getViewMode())
				|| !$eiGuiEntry->getEiEntry()->getEiEntryAccess()->isEiPropWritable($eiPropPath);
		
		$eiu = new Eiu($eiFrame, $eiGuiMaskDeclaration, $eiGuiEntry, $eiPropPath, new DefPropPath([$eiPropPath]));
				
		$guiField = $eiGuiMaskDeclaration->getGuiFieldAssembler($eiPropPath)->buildGuiField($eiu, $readOnly);
		
		if ($guiField === null) {
			return null;
		}
				
		$siField = $guiField->getSiField();
		if ($siField === null || !$readOnly || $siField->isReadOnly()) {
			return $guiField;
		}
		
		throw new GuiBuildFailedException('GuiField of ' . $eiPropPath . ' must have a read-only SiField.');
	}
	
// 	static function createGuiFieldMap(EiGuiValueBoundary $eiGuiValueBoundary, DefPropPath $baseDefPropPath) {
// 		new GuiFieldMap($eiGuiValueBoundary, $forkDefPropPath);
// 	}
}


// class ModEiGuiListener implements EiGuiListener {
// 	private $eiModificatorCollection;
	
// 	public function __construct(EiModCollection $eiModificatorCollection) {
// 		$this->eiModificatorCollection = $eiModificatorCollection;
// 	}
	
// 	public function onInitialized(EiGuiMaskDeclaration $eiGuiMaskDeclaration) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onEiGuiMaskDeclarationInitialized($eiGuiMaskDeclaration);
// 		}
// 	}
	
// 	public function onNewEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onNewEiGuiValueBoundary($eiGuiValueBoundary);
// 		}
// 	}
	
// 	public function onNewView(HtmlView $view) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onNewView($view);
// 		}
// 	}

// }