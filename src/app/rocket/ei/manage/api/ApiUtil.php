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
namespace rocket\ei\manage\api;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\gui\EiEntryGui;
use n2n\util\type\ArgUtils;
use rocket\si\structure\SiBulkyDeclaration;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\EiEngineUtil;
use rocket\si\content\SiEntry;
use rocket\ei\manage\gui\EiGui;
use rocket\si\structure\SiCompactDeclaration;
use rocket\si\content\SiPartialContent;

class ApiUtil {
	private $eiFrame;
	private $eiEngineUtil;
	
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
		$this->eiEngineUtil = new EiEngineUtil($eiFrame->getContextEiEngine(), $eiFrame->getManageState());
	}
	
	/**
	 * @param EiEntryGui[]
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function createMulitBuildupSiBulkyDeclaration(array $eiEntryGuis) {
		$declaration = new SiBulkyDeclaration();
		
		foreach ($eiEntryGuis as $eiEntryGui) {
			ArgUtils::assertTrue($eiEntryGui instanceof EiEntryGui
					&& ViewMode::isBulky($eiEntryGui->getEiGui()->getViewMode()));
			$declaration->putFieldStructureDeclaration(
					$eiEntryGui->getEiEntry()->getEiType()->getId(), 
					$eiEntryGui->getEiGui()->getEiGuiSiFactory()->getSiFieldStructureDeclarations());
		}
		
		return $declaration;
	}
	
	/**
	 * @param array $eiEntryGuis
	 * @return \rocket\si\structure\SiBulkyDeclaration
	 */
	function createMultiBuildupSiCompactDeclaration(array $eiEntryGuis) {
		$declaration = new SiBulkyDeclaration();
		
		foreach ($eiEntryGuis as $eiEntryGui) {
			ArgUtils::assertTrue($eiEntryGui instanceof EiEntryGui);
			$declaration->putFieldStructureDeclaration(
					$eiEntryGui->getEiEntry()->getEiType()->getId(),
					$eiEntryGui->getEiGui()->getEiGuiSiFactory()->getSiFieldStructureDeclarations());
		}
		
		return $declaration;
	}
	
	/**
	 * @param EiGui $eiGui
	 * @return SiCompactDeclaration
	 */
	function createSiCompactDelcaration(EiGui $eiGui) {
		$declaration = new SiCompactDeclaration();
		$declaration->putFieldDeclarations(
				$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getId(),
				$eiGui->getEiGuiSiFactory()->getSiFieldDeclarations());
		
		return $declaration;
	}
	
	/**
	 * @param EiGui $eiGui
	 * @return SiBulkyDeclaration
	 */
	function createSiBulkyDelcaration(EiGui $eiGui) {
		$declaration = new SiBulkyDeclaration();
		$declaration->putFieldStructureDeclarations(
				$this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getId(),
				$eiGui->getEiGuiSiFactory()->getSiFieldStructureDeclarations());
		
		return $declaration;
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return \rocket\si\content\SiQualifier
	 */
	function createSiQualifier(EiObject $eiObject) {
		return $eiObject->createSiQualifier($this->eiEngineUtil->createIdName(), true);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param array $eiEntryGuis
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry(EiObject $eiObject, array $eiEntryGuis) {
		$siEntry = new SiEntry($eiObject->createSiQualifier($eiObject), $inputAvailable);
		
		foreach ($eiEntryGuis as $eiEntryGui) {
			ArgUtils::assertTrue($eiEntryGui instanceof EiEntryGui);
			$declaration->putFieldStructureDeclaration(
					$eiEntryGui->getEiEntry()->getEiType()->getId(),
					$eiEntryGui->getEiGui()->getEiGuiSiFactory()->getSiFieldStructureDeclarations());
		}
		
		return $siEntry;
	}
	
	/**
	 * @param int $offset
	 * @param int $count
	 * @param EiGui $eiGui
	 * @return \rocket\si\content\SiPartialContent
	 */
	function createSiPartialContent(int $offset, int $count, EiGui $eiGui) {
		$content = new SiPartialContent($count);
		$content->setOffset($offset);
		$content->setEntries($eiGui->createSiEntries());
		return $content;
	}
	
}