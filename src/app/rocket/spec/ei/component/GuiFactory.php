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

use rocket\spec\ei\manage\gui\GuiFieldAssembler;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\component\field\GuiEiProp;
use rocket\spec\ei\manage\gui\EditableWrapper;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\gui\GuiPropFork;
use rocket\spec\ei\manage\gui\GuiProp;
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
			
			if (null !== ($guiProp = $eiProp->getGuiProp())){
				ArgUtils::valTypeReturn($guiProp, GuiProp::class, $eiProp, 'getGuiProp');
			
				$guiDefinition->putLevelGuiProp($id, $guiProp, EiPropPath::from($eiProp));
			}
			
			if (null !== ($guiPropFork = $eiProp->getGuiPropFork())){
				ArgUtils::valTypeReturn($guiPropFork, GuiPropFork::class, $eiProp, 'getGuiPropFork');
				
				$guiDefinition->putLevelGuiPropFork($id, $guiPropFork);
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
		
		$guiFieldAssembler = new GuiFieldAssembler($eiMask->getEiEngine()->getGuiDefinition(), $eiuEntryGui);
		
		foreach ($guiIdPaths as $guiIdPath) {
			$result = $guiFieldAssembler->assembleGuiField($guiIdPath);
			if ($result === null) continue;
			
			$eiObjectGui->putDisplayable($guiIdPath, $result->getDisplayable());
			if (null !== ($eiFieldWrapper = $result->getEiFieldWrapper())) {
				$eiObjectGui->putEiFieldWrapper($guiIdPath, $eiFieldWrapper);
			}
			
			if (null !== ($magPropertyPath = $result->getMagPropertyPath())) {
				$eiObjectGui->putEditableWrapper($guiIdPath, new EditableWrapper($result->isMandatory(), 
						$magPropertyPath, $result->getMagWrapper()));
			}
		}
		
		if (null !== ($dispatchable = $guiFieldAssembler->getDispatchable())) {
			$eiObjectGui->setDispatchable($guiFieldAssembler->getDispatchable());
			$eiObjectGui->setForkMagPropertyPaths($guiFieldAssembler->getForkedMagPropertyPaths());
			$eiObjectGui->setSavables($guiFieldAssembler->getSavables());
		}
		
		foreach ($this->eiModificatorCollection as $eiModificator) {
			$eiModificator->setupEiEntryGui($eiObjectGui);
		}
		
		$eiObjectGui->markInitialized();
		
		return $eiObjectGui;
	}
}
