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
use rocket\op\ei\manage\gui\EiEntryGuiAssembler;
use rocket\op\ei\manage\gui\EiEntryGui;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\EiGuiFrame;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use rocket\op\ei\manage\idname\IdNameDefinition;

class IdNameFactory {
	private $eiMask;
	
	public function __construct(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	

	
// 	/**
// 	 * @param EiMask $eiMask
// 	 * @param EiuEntry $eiuEntry
// 	 * @param int $viewMode
// 	 * @param array $eiPropPaths
// 	 * @return EiEntryGui
// 	 */
// 	public static function createEiEntryGui(EiGuiFrame $eiGuiFrame, EiEntry $eiEntry, array $defPropPaths, int $treeLevel = null) {
// 		ArgUtils::valArrayLike($defPropPaths, DefPropPath::class);
		
// 		$eiEntryGui = new EiEntryGui($eiGuiFrame, $eiEntry, $treeLevel);
		
// 		$guiFieldAssembler = new EiEntryGuiAssembler($eiEntryGui);
		
// 		foreach ($defPropPaths as $defPropPath) {
// 			$guiFieldAssembler->assembleGuiField($defPropPath);
// 		}
		
// 		$guiFieldAssembler->finalize();
		
// 		return $eiEntryGui;
// 	}
}


// class ModEiGuiListener implements EiGuiListener {
// 	private $eiModificatorCollection;

// 	public function __construct(EiModCollection $eiModificatorCollection) {
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