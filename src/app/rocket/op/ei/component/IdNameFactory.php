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

use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;

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
// 	 * @return EiGuiValueBoundary
// 	 */
// 	public static function createEiGuiValueBoundary(EiGuiMaskDeclaration $eiGuiMaskDeclaration, EiEntry $eiEntry, array $defPropPaths, int $treeLevel = null) {
// 		ArgUtils::valArrayLike($defPropPaths, DefPropPath::class);
		
// 		$eiGuiValueBoundary = new EiGuiValueBoundary($eiGuiMaskDeclaration, $eiEntry, $treeLevel);
		
// 		$eiGuiField = new EiGuiValueBoundaryAssembler($eiGuiValueBoundary);
		
// 		foreach ($defPropPaths as $defPropPath) {
// 			$eiGuiField->assembleGuiField($defPropPath);
// 		}
		
// 		$eiGuiField->finalize();
		
// 		return $eiGuiValueBoundary;
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