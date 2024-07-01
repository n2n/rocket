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

use rocket\op\ei\EiPropPath;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\util\Eiu;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\ui\gui\ViewMode;
use rocket\ui\gui\field\GuiField;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\GuiEntry;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\op\ei\manage\gui\EiGuiBuildFailedException;
use rocket\op\ei\manage\gui\EiGuiDefinition;
use rocket\ui\gui\control\GuiControlMap;
use rocket\op\ei\manage\api\ApiController;
use rocket\ui\gui\control\GuiControlPath;
use rocket\op\ei\manage\api\ApiControlCallId;

class EiGuiEntryFactory {
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
		
// // 		foreach ($guiDefinition->getEiGuiDefinitionListeners() as $listener) {
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

	public static function createGuiEntry(EiFrame $eiFrame, EiGuiDefinition $eiGuiDefinition,
			EiEntry $eiEntry, bool $entryGuiControlsIncluded): GuiEntry {

		$n2nLocale = $eiFrame->getN2nContext()->getN2nLocale();
		$pid = null;
		$idName = null;
		if (!$eiEntry->isNew()) {
			$pid = $eiEntry->getPid();
			$deterIdNameDefinition = $eiEntry->getEiMask()->getEiEngine()->getIdNameDefinition();
			$idName = $deterIdNameDefinition->createIdentityString($eiEntry->getEiObject(),
					$eiFrame->getN2nContext(), $n2nLocale);
		}

		$guiEntry = new GuiEntry(new SiEntryQualifier($eiGuiDefinition->createSiMaskIdentifier(), $pid, $idName));
		$guiEntry->setModel($eiEntry);
		
		$guiFieldMap = new GuiFieldMap();
		foreach ($eiGuiDefinition->getEiPropPaths() as $eiPropPath) {
			$guiField = self::buildGuiField($eiFrame, $eiGuiDefinition, $guiEntry, $eiEntry, $eiPropPath);
			
			if ($guiField !== null) {
				$guiFieldMap->putGuiField($eiPropPath, $guiField);	
			}
		}

		$guiControlMap = null;
		if ($entryGuiControlsIncluded) {
			$guiControlMap = $eiGuiDefinition->createEntryGuiControlsMap($eiFrame, $eiEntry);
		}

		$guiEntry->init($guiFieldMap, $guiControlMap);

		return $guiEntry;
	}
	

	private static function buildGuiField(EiFrame $eiFrame, EiGuiDefinition $eiGuiDefinition,
			GuiEntry $guiEntry, EiEntry $eiEntry, EiPropPath $eiPropPath): ?GuiField {
		$readOnly = ViewMode::isReadOnly($eiGuiDefinition->getViewMode())
				|| !$eiEntry->getEiEntryAccess()->isEiPropWritable($eiPropPath);
		
		$eiu = new Eiu($eiFrame, $eiGuiDefinition, $eiEntry, $eiPropPath, new DefPropPath([$eiPropPath]));
				
		$eiGuiField = $eiGuiDefinition->getGuiPropWrapper($eiPropPath)->buildEiGuiField($eiFrame, $eiEntry, $readOnly);
		
		if ($eiGuiField === null) {
			return null;
		}
				
		$guiField = $eiGuiField->getGuiField();
		$siField = $guiField->getSiField();
		if ($siField === null || !$readOnly || $siField->isReadOnly()) {
			return $guiField;
		}
		
		throw new EiGuiBuildFailedException('GuiField of ' . $eiPropPath . ' must have a read-only SiField.');
	}

	function createEntryGuiControlsMap(EiFrame $eiFrame, EiGuiDefinition $eiGuiDefinition, EiEntry $eiEntry): GuiControlMap {
		$guiControlsMap = new GuiControlMap();

		$guiControls = [];
		foreach ($eiGuiDefinition->getGuiCommands() as $eiCmdPathStr => $guiCommand) {
			$eiCmdPath = $this->eiCmdPaths[$eiCmdPathStr];
			$eiu = new Eiu($eiFrame, $eiGuiDefinition, $eiEntry, $eiCmdPath);

			$apiUrl = $eiFrame->getApiUrl($eiCmdPath, ApiController::API_CONTROL_SECTION);

			foreach ($this->extractEntryGuiControls($guiCommand, $eiCmdPathStr, $eiu) as $entryGuiControl) {
				$guiControlPath = new GuiControlPath([$eiCmdPathStr, $entryGuiControl->getId()]);
				$apiControlCallId = ApiControlCallId::create($this->eiMask, $guiControlPath, $eiEntry);

				$guiControlsMap->putGuiControl($guiControlPath, $entryGuiControl, $apiControlCallId, $apiUrl);
			}
		}

		return $guiControlsMap;
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