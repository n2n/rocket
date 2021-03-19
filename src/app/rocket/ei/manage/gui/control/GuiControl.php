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
namespace rocket\ei\manage\gui\control;

use rocket\si\control\SiControl;
use rocket\ei\manage\api\ApiControlCallId;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\manage\entry\EiEntry;
use rocket\si\control\SiControlResult;
use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\api\ZoneApiControlCallId;
use n2n\util\uri\Url;
use rocket\ei\EiCommandPath;

interface GuiControl {
	
	/**
	 * @return string
	 */
	function getId(): string;
	
	function isInputHandled(): bool;
	
	function getChilById(string $id): ?GuiControl;
	
	/**
	 * @param EiGuiModel $eiGuiModel
	 * @return SiControlResult
	 */
	function handle(EiFrame $eiFrame, EiGuiModel $eiGuiModel, array $inputEiEntries): SiResult;
	
	/**
	 * @param EiGuiModel $eiGuiModel
	 * @param EiEntry $eiEntry
	 * @return SiControlResult
	 */
	function handleEntry(EiFrame $eiFrame, EiGuiModel $eiGuiModel, EiEntry $eiEntry): SiResult;
	
	
// 	/**
// 	 * @param EiGuiModel $eiGuiModel
// 	 * @param EiEntry[] $eiEntries
// 	 * @return SiResult
// 	 */
// 	function handleEntries(EiFrame $eiFrame, EiGuiModel $eiGuiModel, array $eiEntries): SiResult;
	
	/**
	 * @param GuiControlPath $guiCommandPath
	 * @param ApiControlCallId $siApiCallId
	 * @return SiControl
	 */
	function toCmdSiControl(ApiControlCallId $siApiCallId): SiControl;
	
	/**
	 * @param Url $zoneUrl
	 * @param ZoneApiControlCallId $zoneControllCallId
	 * @return SiControl
	 */
	function toZoneSiControl(Url $zoneUrl, ZoneApiControlCallId $zoneControllCallId): SiControl;
}