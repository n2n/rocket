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
namespace rocket\op\ei\manage\api;

use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\EiGuiValueBoundary;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\ui\si\content\SiPartialContent;

class ApiUtil {
//	private $eiFrame;
//	private $eiEngineUtil;
	
	function __construct(EiFrame $eiFrame) {
//		$this->eiFrame = $eiFrame;
//		$this->eiEngineUtil = new EiEngineUtil($eiFrame->getContextEiEngine(), $eiFrame->getEiLaunch());
	}
	
// 	/**
// 	 * @param EiGuiValueBoundary[]
// 	 * @return \rocket\op\ei\manage\gui\EiGuiMaskDeclaration
// 	 */
// 	function createMultiBuildupSiDeclaration(array $eiGuiValueBoundaries) {
// 		$declaration = new SiDeclaration();
		
// 		foreach ($eiGuiValueBoundaries as $eiGuiValueBoundary) {
// 			ArgUtils::assertTrue($eiGuiValueBoundary instanceof EiGuiValueBoundary);
			
// 			$declaration->addTypeDeclaration($eiGuiValueBoundary->getEiGuiMaskDeclaration()->createSiTypDeclaration());
// 		}
		
// 		return $declaration;
// 	}
	
	
// 	/**
// 	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
// 	 * @return SiDeclaration
// 	 */
// 	function createSiDeclaration(EiGuiMaskDeclaration $eiGuiMaskDeclaration) {
// 		$typeId = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getId();
		
// 		$declaration = new SiDeclaration();
// 		$declaration->putFieldDeclarations($typeId, $eiGuiMaskDeclaration->getEiGuiSiFactory()->getSiProps());
// 		$declaration->putFieldStructureDeclarations($typeId, $eiGuiMaskDeclaration->getEiGuiSiFactory()->getSiStructureDeclarations());
		
// 		return $declaration;
// 	}

	
//	/**
//	 * @param EiObject $eiObject
//	 * @return \rocket\si\content\SiEntryQualifier
//	 */
//	function createSiEntryQualifier(EiObject $eiObject) {
//		return $eiObject->createSiEntryQualifier($this->eiEngineUtil->createIdName(), true);
//	}
	
// 	/**
// 	 * @param EiObject $eiObject
// 	 * @param array $eiGuiValueBoundaries
// 	 * @return \rocket\si\content\SiEntry
// 	 */
// 	function createSiEntry(EiObject $eiObject, array $eiGuiValueBoundaries) {
// 		$siValueBoundary = new SiEntry($eiObject->createSiEntryQualifier($eiObject), 
// 				ViewMode::isReadOnly($this->eiGuiMaskDeclaration->getViewMode()));
		
// 		foreach ($eiGuiValueBoundaries as $eiGuiValueBoundary) {
// 			ArgUtils::assertTrue($eiGuiValueBoundary instanceof EiGuiValueBoundary);
// 			$declaration->putFieldStructureDeclarations(
// 					$eiGuiValueBoundary->getEiEntry()->getEiType()->getId(),
// 					$eiGuiValueBoundary->getEiGuiMaskDeclaration()->getEiGuiSiFactory()->getSiStructureDeclarations());
// 		}
		
// 		return $siValueBoundary;
// 	}

	/**
	 * @param int $offset
	 * @param int $count
	 * @param array $eiGuiValueBoundaries
	 * @return SiPartialContent
	 */
	function createSiPartialContent(int $offset, int $count, array $eiGuiValueBoundaries) {
		$content = new SiPartialContent($count);
		$content->setOffset($offset);
		$content->setValueBoundaries(array_map(fn (EiGuiValueBoundary $b) => $b->createSiValueBoundary(),
				$eiGuiValueBoundaries));
		return $content;
	}
	
}