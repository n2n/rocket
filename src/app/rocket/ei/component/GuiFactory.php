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
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\EiEntryGuiAssembler;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\EiPropPath;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\GuiEiPropFork;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\component\command\GuiEiCommand;

class GuiFactory {
	private $eiMask;
	
	public function __construct(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	
	/**
	 * @param EiFrame $eiFrame
	 * @param int $viewMode
	 * @throws \InvalidArgumentException
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	public function createEiGui(EiFrame $eiFrame, int $viewMode) {
		if (!$this->eiMask->getEiType()->isA($eiFrame->getContextEiEngine()->getEiMask()->getEiType())) {
			throw new \InvalidArgumentException('Incompatible EiGui');
		}
		
		$guiDefinition = new GuiDefinition();
		$eiGui = new EiGui($eiFrame, $this->eiMask, $guiDefinition, $viewMode);
		
		foreach ($this->eiMask->getEiPropCollection() as $eiPropPathStr => $eiProp) {
			$eiPropPath = $eiProp->getWrapper()->getEiPropPath();
			
			if (($eiProp instanceof GuiEiProp) 
					&& null !== ($guiProp = $eiProp->buildGuiProp(new Eiu($eiGui, $eiPropPath)))) {
				$guiDefinition->putGuiProp($eiPropPath, $guiProp, EiPropPath::from($eiProp));
			}
			
			if (($eiProp instanceof GuiEiPropFork) 
					&& null !== ($guiPropFork = $eiProp->buildGuiPropFork(new Eiu($eiGui, $eiPropPath)))){
				$guiDefinition->putGuiPropFork($eiPropPath, $guiPropFork);
			}
		}
		
		foreach ($this->eiMask->getEiCommandCollection() as $eiCommandPathStr => $eiCommand) {
			$eiCommandPath = $eiCommand->getWrapper()->getEiCommandPath();
			
			if ($eiCommand instanceof GuiEiCommand && null !== ($guiCommand = $eiCommand->buildGuiCommand(new Eiu($eiGui, $eiCommandPath)))) {
				$guiDefinition->putGuiCommand($eiCommandPath, $guiCommand);
			}
		}
		
		foreach ($this->eiMask->getEiModificatorCollection() as $eiModificator) {
			$eiModificator->setupEiGui($eiu);
		}
		
		
// 		if (!$init) {
// 			$this->noInitCb($eiGui);
// 			return $eiGui;
// 		}
		
// 		foreach ($guiDefinition->getGuiDefinitionListeners() as $listener) {
// 			$listener->onNewEiGui($eiGui);
// 		}
		
		if (!$eiGui->isInit()) {
			$this->eiMask->getDisplayScheme()->initEiGui($eiGui, $guiDefinition);
		}
		
		return $eiGui;
	}
	
// 	/**
// 	 * @param EiGui $eiGui
// 	 * @param HtmlView $view
// 	 * @return Control[]
// 	 */
// 	public function createOverallControls(EiGui $eiGui, HtmlView $view) {
// 		$eiu = new Eiu($eiGui);
		
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
	
// 	/**
// 	 * @param EiEntryGui $eiEntryGui
// 	 * @param HtmlView $view
// 	 * @return GuiControl[]
// 	 */
// 	public function createEntryGuiControls(EiEntryGui $eiEntryGui, HtmlView $view) {
// 		$eiu = new Eiu($eiEntryGui);
		
// 		$controls = array();
		
// 		foreach ($this->eiMask->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof EntryGuiControlComponent)
// 					|| !$eiu->entry()->access()->isExecutableBy($eiCommand)) {
// 				continue;
// 			}
			
// 			$entryControls = $eiCommand->createEntryGuiControls($eiu, $view);
// 			ArgUtils::valArrayReturn($entryControls, $eiCommand, 'createEntryGuiControls', Control::class);
// 			foreach ($entryControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
// 			}
// 		}
		
// 		return $this->eiMask->getDisplayScheme()->getEntryGuiControlOrder()->sort($controls);
// 	}
	
	/**
	 * @param EiMask $eiMask
	 * @param EiuEntry $eiuEntry
	 * @param int $viewMode
	 * @param array $eiPropPaths
	 * @return EiEntryGui
	 */
	public static function createEiEntryGui(EiGui $eiGui, EiEntry $eiEntry, array $guiFieldPaths, int $treeLevel = null) {
		ArgUtils::valArrayLike($guiFieldPaths, GuiFieldPath::class);
		
		$eiEntryGui = new EiEntryGui($eiGui, $eiEntry, $treeLevel);
		
		$guiFieldAssembler = new EiEntryGuiAssembler($eiEntryGui);
				
		foreach ($guiFieldPaths as $guiFieldPath) {
			$guiFieldAssembler->assembleGuiField($guiFieldPath);
		}
		
		$guiFieldAssembler->finalize();
				
		return $eiEntryGui;
	}
}


// class ModEiGuiListener implements EiGuiListener {
// 	private $eiModificatorCollection;
	
// 	public function __construct(EiModificatorCollection $eiModificatorCollection) {
// 		$this->eiModificatorCollection = $eiModificatorCollection;
// 	}
	
// 	public function onInitialized(EiGui $eiGui) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onEiGuiInitialized($eiGui);
// 		}
// 	}
	
// 	public function onNewEiEntryGui(EiEntryGui $eiEntryGui) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onNewEiEntryGui($eiEntryGui);
// 		}
// 	}
	
// 	public function onNewView(HtmlView $view) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onNewView($view);
// 		}
// 	}

// }