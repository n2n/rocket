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
use rocket\ei\manage\gui\EiEntryGuiAssembler;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\EiPropPath;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\util\Eiu;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\component\prop\IdNameEiProp;
use rocket\ei\component\prop\IdNameEiPropFork;
use n2n\core\container\N2nContext;
use rocket\ei\manage\idname\IdNameDefinition;

class IdNameFactory {
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
	public function createIdNameDefinition(N2nContext $n2nContext) {
		$idNameDefinition = new IdNameDefinition($this->eiMask->getLabelLstr());
		
		foreach ($this->eiMask->getEiPropCollection() as $eiPropPathStr => $eiProp) {
			$eiPropPath = $eiProp->getWrapper()->getEiPropPath();
			
			if (($eiProp instanceof IdNameEiProp)
					&& null !== ($guiProp = $eiProp->buildGuiProp(new Eiu($n2nContext, $eiPropPath)))) {
				$idNameDefinition->putGuiProp($eiPropPath, $guiProp, EiPropPath::from($eiProp));
			}
			
			if (($eiProp instanceof IdNameEiPropFork)
					&& null !== ($guiPropFork = $eiProp->buildGuiPropFork(new Eiu($n2nContext, $eiPropPath)))){
				$idNameDefinition->putGuiPropFork($eiPropPath, $guiPropFork);
			}
		}
		
// 		foreach ($this->eiMask->getEiModificatorCollection() as $eiModificator) {
// 			$eiModificator->setupIdNameDefinition($eiu);
// 		}
		
		return $idNameDefinition;
	}
	
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
		
		foreach ($guiFieldPaths as $guiPropPath) {
			$guiFieldAssembler->assembleGuiField($guiPropPath);
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