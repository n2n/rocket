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
namespace rocket\ei\manage\gui;

use rocket\ei\manage\frame\EiFrame;
use rocket\si\content\impl\basic\CompactEntrySiComp;
use rocket\si\content\impl\basic\BulkyEntrySiComp;
use rocket\si\content\impl\basic\CompactExplorerSiComp;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\si\content\SiPartialContent;

class EiGuiUtil {
	private $eiGui;
	private $eiFrame;
	
	function __construct(EiGui $eiGui, EiFrame $eiFrame) {
		$this->eiGui = $eiGui;
		$this->eiFrame = $eiFrame;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createCompactEntrySiComp(bool $entrySiControlsIncluded = true) {
		$siComp = new CompactEntrySiComp($this->eiGui->getEiGuiModel()->createSiDeclaration($this->eiFrame),
				$this->eiGui->createSiEntry($this->eiFrame, $entrySiControlsIncluded));
		
// 		if ($generalSiControlsIncluded) {
// 			$siComp->setControls($this->eiGui->getEiGuiFrame()->createGeneralSiControls($this->eiFrame));
// 		}
		
		return $siComp;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createBulkyEntrySiComp(/*bool $generalSiControlsIncluded = true,*/
			bool $entrySiControlsIncluded = true) {
		$siComp = new BulkyEntrySiComp($this->eiGui->getEiGuiModel()->createSiDeclaration($this->eiFrame),
				$this->eiGui->createSiEntry($this->eiFrame, $entrySiControlsIncluded));
		
// 		if ($generalSiControlsIncluded) {
// 			$siComp->setControls($this->eiGui->getEiGuiFrame()->createGeneralSiControls($this->eiFrame));
// 		}
		
		return $siComp;
	}
	
	function createCompactExplorerSiComp(int $pageSize, bool $entrySiControlsIncluded) {
		$eiFrameUtil = new EiFrameUtil($this->eiFrame);
				
		$siDeclaration = $this->eiGui->getEiGuiModel()->createSiDeclaration($this->eiFrame);
		$siPartialContent = new SiPartialContent($eiFrameUtil->count(), 
				$this->eiGui->createSiEntries($this->eiFrame, $entrySiControlsIncluded));
		$siComp = new CompactExplorerSiComp($this->eiFrame->getApiUrl(), $pageSize, $siDeclaration, $siPartialContent);
		
		return $siComp;
	}
}