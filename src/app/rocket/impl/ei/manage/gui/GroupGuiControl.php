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
use rocket\ui\si\control\SiControl;
use rocket\ui\si\control\SiCallResponse;
use rocket\ui\si\control\SiButton;
use rocket\op\ei\manage\api\ApiControlCallId;
use rocket\ui\gui\EiGuiDeclaration;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\control\GuiControl;
use rocket\ui\si\control\impl\GroupSiControl;
use n2n\util\ex\UnsupportedOperationException;
use rocket\op\ei\manage\api\ZoneApiControlCallId;
use n2n\util\uri\Url;
use rocket\ui\gui\control\GuiControlMap;

class GroupGuiControl implements GuiControl {
	private $id;
	private $siButton;
	private GuiControlMap $forkGuiControlMap;
	
	function __construct(string $id, SiButton $siButton) {
		$this->id = $id;
		$this->siButton = $siButton;
	}
	
	function getId(): string {
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\control\GuiControl::isInputHandled()
	 */
	public function isInputHandled(): bool {
		return false;
	}

	function putGuiControl(string $controlName, GuiControl $guiControl): static {
		$this->forkGuiControlMap->putGuiControl($controlName, $guiControl);
		return $this;
	}

	function getSiControl(Url $apiUrl, ApiControlCallId|ZoneApiControlCallId $siApiCallId): SiControl {
		return new GroupSiControl($this->siButton, 
				array_map(function ($child) use ($apiUrl, $siApiCallId) {
					return $child->toSiControl($apiUrl, $siApiCallId->guiControlPathExt($child->getId()));
				}, $this->childrean));;
	}


}