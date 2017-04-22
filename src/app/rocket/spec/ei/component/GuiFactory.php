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

use rocket\spec\ei\component\field\EiPropCollection;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\gui\GuiDefinition;

use rocket\spec\ei\manage\gui\GuiElementAssembler;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\component\field\GuiEiProp;
use rocket\spec\ei\manage\gui\EditableWrapper;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\gui\GuiFieldFork;
use rocket\spec\ei\manage\gui\GuiField;
use rocket\spec\ei\manage\util\model\EiuEntry;
use rocket\spec\ei\manage\util\model\EiuEntryGui;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\gui\GuiIdPath;

class GuiFactory {
	private $eiPropCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiPropCollection $eiPropCollection, 
			EiModificatorCollection $eiModificatorCollection) {
		$this->eiPropCollection = $eiPropCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}
	
	public function createGuiDefinition() {
		$guiDefinition = new GuiDefinition();
		
		foreach ($this->eiPropCollection as $id => $eiProp) {
			if (!($eiProp instanceof GuiEiProp)) continue;
			
			if (null !== ($guiField = $eiProp->getGuiField())){
				ArgUtils::valTypeReturn($guiField, GuiField::class, $eiProp, 'getGuiField');
			
				$guiDefinition->putLevelGuiField($id, $guiField, EiPropPath::from($eiProp));
			}
			
			if (null !== ($guiFieldFork = $eiProp->getGuiFieldFork())){
				ArgUtils::valTypeReturn($guiFieldFork, GuiFieldFork::class, $eiProp, 'getGuiFieldFork');
				
				$guiDefinition->putLevelGuiFieldFork($id, $guiFieldFork);
			}
		}
		
		foreach ($this->eiModificatorCollection as $modificator) {
			$modificator->setupGuiDefinition($guiDefinition);
		}
		
		return $guiDefinition;
	}
	
	/**
	 * @param EiMask $eiMask
	 * @param EiuEntry $eiuEntry
	 * @param int $viewMode
	 * @param array $guiIdPaths
	 * @return EiEntryGui
	 */
	public function createEiEntryGui(EiMask $eiMask, EiuEntry $eiuEntry, int $viewMode, array $guiIdPaths) {
		ArgUtils::valArrayLike($guiIdPaths, GuiIdPath::class);
		
		$eiObjectGui = new EiEntryGui($eiMask, $viewMode);
		$eiuEntryGui = new EiuEntryGui($eiObjectGui, $eiuEntry);
		
		$guiElementAssembler = new GuiElementAssembler($eiMask->getEiEngine()->getGuiDefinition(), $eiuEntryGui);
		
		foreach ($guiIdPaths as $guiIdPath) {
			$result = $guiElementAssembler->assembleGuiElement($guiIdPath);
			if ($result === null) continue;
			
			$eiObjectGui->putDisplayable($guiIdPath, $result->getDisplayable());
			if (null !== ($mappableWrapper = $result->getMappableWrapper())) {
				$eiObjectGui->putMappableWrapper($guiIdPath, $mappableWrapper);
			}
			
			if (null !== ($magPropertyPath = $result->getMagPropertyPath())) {
				$eiObjectGui->putEditableWrapper($guiIdPath, new EditableWrapper($result->isMandatory(), 
						$magPropertyPath, $result->getMagWrapper()));
			}
		}
		
		if (null !== ($dispatchable = $guiElementAssembler->getDispatchable())) {
			$eiObjectGui->setDispatchable($guiElementAssembler->getDispatchable());
			$eiObjectGui->setForkMagPropertyPaths($guiElementAssembler->getForkedMagPropertyPaths());
			$eiObjectGui->setSavables($guiElementAssembler->getSavables());
		}
		
		foreach ($this->eiModificatorCollection as $eiModificator) {
			$eiModificator->setupEiEntryGui($eiObjectGui);
		}
		
		$eiObjectGui->markInitialized();
		
		return $eiObjectGui;
	}
}
