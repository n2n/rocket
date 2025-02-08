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

use rocket\ui\si\control\SiControl;
use rocket\ui\si\control\SiButton;
use rocket\op\ei\manage\api\ApiControlCallId;
use rocket\ui\gui\EiGuiDeclaration;
use rocket\ui\gui\control\GuiControl;
use rocket\ui\si\control\impl\GroupSiControl;
use rocket\op\ei\manage\api\ZoneApiControlCallId;
use n2n\util\uri\Url;
use rocket\ui\gui\control\GuiControlMap;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\ui\gui\control\GuiControlKey;

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
		$this->forkGuiControlMap->putGuiControl(new GuiControlKey($controlName), $guiControl);
		return $this;
	}

	function getSiControl(): SiControl {
		return new GroupSiControl($this->siButton, 
				array_map(function ($child) {
					return $child->getSiControl();
				}, $this->forkGuiControlMap->getGuiControls()));
	}


	function handleCall(): SiCallResponse {
		// TODO: Implement handleCall() method.
	}

	function getForkGuiControlMap(): ?GuiControlMap {
		return $this->forkGuiControlMap;
	}
}