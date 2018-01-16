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
namespace rocket\spec\ei\component;

use rocket\spec\ei\component\prop\EiPropCollection;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\gui\GuiFieldAssembler;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\component\prop\GuiEiProp;
use rocket\spec\ei\manage\gui\EditableWrapper;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\gui\GuiPropFork;
use rocket\spec\ei\manage\gui\GuiProp;
use rocket\spec\ei\manage\util\model\EiuEntry;
use rocket\spec\ei\manage\util\model\EiuEntryGui;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\EiGui;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\gui\EiGuiListener;
use rocket\spec\ei\manage\mapping\EiEntry;

class GuiFactory {
	private $eiPropCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiPropCollection $eiPropCollection, EiModificatorCollection $eiModificatorCollection) {
		$this->eiPropCollection = $eiPropCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}
	
	public function createGuiDefinition() {
		$guiDefinition = new GuiDefinition();
		
		foreach ($this->eiPropCollection as $id => $eiProp) {
			if (!($eiProp instanceof GuiEiProp)) continue;
			
			if (null !== ($guiProp = $eiProp->getGuiProp())){
				ArgUtils::valTypeReturn($guiProp, GuiProp::class, $eiProp, 'getGuiProp');
			
				$guiDefinition->putLevelGuiProp($id, $guiProp, EiPropPath::from($eiProp));
			}
			
			if (null !== ($guiPropFork = $eiProp->getGuiPropFork())){
				ArgUtils::valTypeReturn($guiPropFork, GuiPropFork::class, $eiProp, 'getGuiPropFork');
				
				$guiDefinition->putLevelGuiPropFork($id, $guiPropFork, EiPropPath::from($eiProp));
			}
		}
		
		foreach ($this->eiModificatorCollection as $eiModificator) {
			$eiModificator->setupGuiDefinition($guiDefinition);
		}
		
		return $guiDefinition;
	}
	
// 	public function createEiGui(EiFrame $eiFrame, GuiDefinition $guiDefinition, int $viewMode, 
// 			EiGuiViewFactory $eiGuiViewFactory) {
// 		$eiGui = new EiGui($eiFrame, $guiDefinition, $viewMode, $eiGuiViewFactory);
// 		$eiGui->registerListner(new ModEiGuiListener($this->eiModificatorCollection));
// 		return $eiGui;
// 	}
	
	/**
	 * @param EiMask $eiMask
	 * @param EiuEntry $eiuEntry
	 * @param int $viewMode
	 * @param array $guiIdPaths
	 * @return EiEntryGui
	 */
	public static function createEiEntryGui(EiGui $eiGui, EiEntry $eiEntry, array $guiIdPaths, int $treeLevel = null) {
		ArgUtils::valArrayLike($guiIdPaths, GuiIdPath::class);
		
		$eiEntryGui = new EiEntryGui($eiGui, $eiEntry, $treeLevel);
		$eiuEntryGui = new EiuEntryGui($eiEntryGui);
		
		$guiFieldAssembler = new GuiFieldAssembler($eiGui->getEiGuiViewFactory()->getGuiDefinition(), $eiuEntryGui);
		
		foreach ($guiIdPaths as $guiIdPath) {
			$result = $guiFieldAssembler->assembleGuiField($guiIdPath);
			if ($result === null) continue;
			
			$eiEntryGui->putDisplayable($guiIdPath, $result->getDisplayable());
			if (null !== ($eiFieldWrapper = $result->getEiFieldWrapper())) {
				$eiEntryGui->putEiFieldWrapper($guiIdPath, $eiFieldWrapper);
			}
			
			if (null !== ($magPropertyPath = $result->getMagPropertyPath())) {
				$eiEntryGui->putEditableWrapper($guiIdPath, new EditableWrapper($result->isMandatory(), 
						$magPropertyPath, $result->getMagWrapper()));
			}
		}
		
		if (null !== ($dispatchable = $guiFieldAssembler->getDispatchable())) {
			$eiEntryGui->setDispatchable($guiFieldAssembler->getDispatchable());
			$eiEntryGui->setForkMagPropertyPaths($guiFieldAssembler->getForkedMagPropertyPaths());
			$eiEntryGui->setSavables($guiFieldAssembler->getSavables());
		}
				
		return $eiEntryGui;
	}
}


class ModEiGuiListener implements EiGuiListener {
	private $eiModificatorCollection;
	
	public function __construct(EiModificatorCollection $eiModificatorCollection) {
		$this->eiModificatorCollection = $eiModificatorCollection;
	}
	
	public function onNewEiEntryGui(EiEntryGui $eiEntryGui) {
		foreach ($this->eiModificatorCollection as $eiModificator) {
			$eiModificator->onNewEiEntryGui($eiEntryGui);
		}
	}
	
	public function onNewView(HtmlView $view) {
		foreach ($this->eiModificatorCollection as $eiModificator) {
			$eiModificator->onNewView($view);
		}
	}
}