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
namespace rocket\ui\gui\control;

use rocket\ui\si\control\SiControl;
use rocket\ui\gui\EiGuiDeclaration;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\ui\gui\field\GuiFieldMap;

interface GuiControl {
	
	function isInputHandled(): bool;


	function handleCall(): SiCallResponse;
	
//	function handleEntry(EiFrame $eiFrame, EiGuiDeclaration $eiGuiDeclaration, EiEntry $eiEntry): SiCallResponse;
	
	
// 	/**
// 	 * @param EiGuiDeclaration $eiGuiDeclaration
// 	 * @param EiEntry[] $eiEntries
// 	 * @return SiCallResponse
// 	 */
// 	function handleEntries(EiFrame $eiFrame, EiGuiDeclaration $eiGuiDeclaration, array $eiEntries): SiCallResponse;

	function getSiControl(/*Url $apiUrl, ApiControlCallId|ZoneApiControlCallId $siApiCallId*/): SiControl;

	/**
	 * @return GuiFieldMap|NULL
	 */
	function getForkGuiControlMap(): ?GuiControlMap;
}