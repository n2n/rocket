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
namespace rocket\impl\ei\manage\gui;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\si\control\SiControl;
use rocket\si\control\SiCallResponse;
use n2n\util\ex\NotYetImplementedException;
use rocket\si\control\SiButton;
use rocket\op\ei\manage\api\ApiControlCallId;
use rocket\op\ei\manage\gui\EiGuiDeclaration;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\si\control\impl\DeactivatedSiControl;
use rocket\op\ei\manage\gui\control\GuiControl;
use n2n\util\uri\Url;
use rocket\op\ei\manage\gui\control\GuiControlPath;
use rocket\op\ei\manage\api\ZoneApiControlCallId;

class DeactivatedGuiControl implements GuiControl {
	private $id;
	private $siButton;
	private $childrean = [];
	
	function __construct(string $id, SiButton $siButton) {
		$this->id = $id;
		$this->siButton = $siButton;
	}
	
	function getId(): string {
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\gui\control\GuiControl::isInputHandled()
	 */
	public function isInputHandled(): bool {
		return false;
	}
	
	function getChildById(string $id): ?GuiControl {
		return null;
	}
	
	function toSiControl(Url $apiUrl, ApiControlCallId|ZoneApiControlCallId $siApiCallId): SiControl {
		return new DeactivatedSiControl($this->siButton);
	}
	
	public function handleEntries(EiFrame $eiFrame, EiGuiDeclaration $eiGuiDeclaration, array $eiEntries): SiCallResponse {
		throw new NotYetImplementedException();
	}

	public function handle(EiFrame $eiFrame, EiGuiDeclaration $eiGuiDeclaration, array $inputEiEntries): SiCallResponse {
		throw new NotYetImplementedException();
	}

	public function handleEntry(EiFrame $eiFrame, EiGuiDeclaration $eiGuiDeclaration, EiEntry $eiEntry): SiCallResponse {
		throw new NotYetImplementedException();
	}
}