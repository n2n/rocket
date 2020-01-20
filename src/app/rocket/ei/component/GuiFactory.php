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
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\EiPropPath;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\util\Eiu;
use rocket\ei\component\command\GuiEiCommand;
use n2n\core\container\N2nContext;
use rocket\ei\manage\gui\GuiFieldMap;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\field\GuiField;

class GuiFactory {
	private $eiMask;
	
	public function __construct(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @return \rocket\ei\manage\gui\GuiDefinition
	 */
	function createGuiDefinition(N2nContext $n2nContext) {
		$guiDefinition = new GuiDefinition($this->eiMask);
		
		foreach ($this->eiMask->getEiPropCollection() as $eiProp) {
			$eiPropPath = $eiProp->getWrapper()->getEiPropPath();
			
			if (($eiProp instanceof GuiEiProp)
					&& null !== ($guiProp = $eiProp->buildGuiProp(new Eiu($n2nContext, $this->eiMask, $eiPropPath)))) {
				$guiDefinition->putGuiProp($eiPropPath, $guiProp, EiPropPath::from($eiProp));
			}
		}
		
		foreach ($this->eiMask->getEiCommandCollection() as $eiCommand) {
			$eiCommandPath = $eiCommand->getWrapper()->getEiCommandPath();
			
			if ($eiCommand instanceof GuiEiCommand 
					&& null !== ($guiCommand = $eiCommand->buildGuiCommand(new Eiu($n2nContext, $this->eiMask, $eiCommandPath)))) {
				$guiDefinition->putGuiCommand($eiCommandPath, $guiCommand);
			}
		}
		
		return $guiDefinition;
	}
	
// 	/**
// 	 * @param EiFrame $eiFrame
// 	 * @param int $viewMode
// 	 * @throws \InvalidArgumentException
// 	 * @return \rocket\ei\manage\gui\EiGuiFrame
// 	 */
// 	function createEiGuiFrame(EiFrame $eiFrame, int $viewMode) {
// 		if (!$this->eiMask->getEiType()->isA($eiFrame->getContextEiEngine()->getEiMask()->getEiType())) {
// 			throw new \InvalidArgumentException('Incompatible EiGuiFrame');
// 		}
		
// 		$eiGuiFrame = new EiGuiFrame($eiFrame, $this->eiMask, $viewMode);
		
// 		$this->eiMask->getEiModificatorCollection()->setupEiGuiFrame($eiGuiFrame);
		
		
// // 		if (!$init) {
// // 			$this->noInitCb($eiGuiFrame);
// // 			return $eiGuiFrame;
// // 		}
		
// // 		foreach ($guiDefinition->getGuiDefinitionListeners() as $listener) {
// // 			$listener->onNewEiGuiFrame($eiGuiFrame);
// // 		}
		
// // 		if (!$eiGuiFrame->isInit()) {
// // 			$this->eiMask->getDisplayScheme()->initEiGuiFrame($eiGuiFrame, $guiDefinition);
// // 		}
		
// 		return $eiGuiFrame;
// 	}
	
// 	/**
// 	 * @param EiGuiFrame $eiGuiFrame
// 	 * @param HtmlView $view
// 	 * @return Control[]
// 	 */
// 	public function createOverallControls(EiGuiFrame $eiGuiFrame, HtmlView $view) {
// 		$eiu = new Eiu($eiGuiFrame);
		
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
	public static function createEiEntryGui(EiGuiFrame $eiGuiFrame, EiEntry $eiEntry, array $guiPropPaths, int $treeLevel = null) {
		ArgUtils::valArrayLike($guiPropPaths, GuiPropPath::class);
		
		$eiEntryGui = new EiEntryGui($eiEntry, $treeLevel);
		
		$guiFieldMap = new GuiFieldMap();
		foreach ($eiGuiFrame->getEiPropPaths() as $eiPropPath) {
			$guiField = self::buildGuiField($eiGuiFrame, $eiEntryGui, $eiPropPath);
			
			if ($guiField !== null) {
				$guiFieldMap->putGuiField($eiPropPath, $guiField);	
			}
		}
		$eiEntryGui->init($guiFieldMap);
				
		return $eiEntryGui;
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param EiEntryGui $eiEntryGui
	 * @param EiPropPath $eiPropPath
	 * @return GuiField|null
	 */
	private static function buildGuiField($eiGuiFrame, $eiEntryGui, $eiPropPath) {
		$readOnly = ViewMode::isReadOnly($eiGuiFrame->getViewMode())
				|| !$eiEntryGui->getEiEntry()->getEiEntryAccess()->isEiPropWritable($eiPropPath);
				
		$guiField = $eiGuiFrame->getGuiFieldAssembler($eiPropPath)
				->buildGuiField(new Eiu($eiGuiFrame, $eiEntryGui, $eiPropPath, new GuiPropPath([$eiPropPath])), $readOnly);
		
		$siField = $guiField->getSiField();
		if ($siField === null || !$readOnly || $siField->isReadOnly()) {
			return $guiField;
		}
		
		throw new GuiException('GuiField of ' . $eiPropPath . ' must have a read-only SiField.');
	}
	
// 	static function createGuiFieldMap(EiEntryGui $eiEntryGui, GuiPropPath $baseGuiPropPath) {
// 		new GuiFieldMap($eiEntryGui, $forkGuiPropPath);
// 	}
}


// class ModEiGuiListener implements EiGuiListener {
// 	private $eiModificatorCollection;
	
// 	public function __construct(EiModificatorCollection $eiModificatorCollection) {
// 		$this->eiModificatorCollection = $eiModificatorCollection;
// 	}
	
// 	public function onInitialized(EiGuiFrame $eiGuiFrame) {
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->onEiGuiFrameInitialized($eiGuiFrame);
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