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
namespace rocket\op\ei\manage\gui\factory;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\GuiEntry;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\op\ei\manage\entry\WrittenMappingListener;

class EiGuiEntryFactory {

	public function __construct(private EiFrame $eiFrame) {
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



	public function createGuiEntry(EiEntry $eiEntry, int $viewMode, bool $entryGuiControlsIncluded): GuiEntry {

		$eiSiEntryQualifierFactory = new EiSiEntryQualifierFactory($this->eiFrame->getN2nContext());

		$eiGuiDefinition = $eiEntry->getEiMask()->getEiEngine()->getEiGuiDefinition($viewMode);
		$guiEntry = new GuiEntry($eiSiEntryQualifierFactory->create($eiEntry, $viewMode));
		$guiEntry->setModel($eiEntry);
		
		$guiFieldMap = $eiGuiDefinition->getEiGuiPropMap()->createGuiFieldMap($this->eiFrame, $eiEntry);

		$guiControlMap = null;
		if ($entryGuiControlsIncluded) {
			$guiControlMap = $eiGuiDefinition->getEiGuiCmdMap()->createEntryGuiControlsMap($this->eiFrame, $eiEntry);
		}

		$guiEntry->init($guiFieldMap, $guiControlMap);

		$eiEntityObj = $eiEntry->getEiObject()->getEiEntityObj();
		if ($eiEntityObj->isPersistent()) {
			return $guiEntry;
		}

		$eiEntityObj->onId(
				fn () => $guiEntry->setSiEntryQualifier($eiSiEntryQualifierFactory->create($eiEntry, $viewMode)));

		return $guiEntry;
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