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
namespace rocket\ei\util\control;

use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\frame\EiFrame;
use rocket\si\control\SiControl;
use rocket\si\control\SiResult;
use n2n\util\ex\NotYetImplementedException;
use n2n\util\uri\Url;
use rocket\si\control\SiButton;
use rocket\si\control\impl\RefSiControl;
use rocket\ei\manage\gui\control\EntryGuiControl;
use rocket\ei\manage\gui\control\GeneralGuiControl;
use rocket\ei\manage\gui\control\SelectionGuiControl;

class EiuRefGuiControl implements GeneralGuiControl, EntryGuiControl, SelectionGuiControl {
	private $id;
	private $url;
	private $siButton;
	private $inputHandled = false;
	
	function __construct(string $id, Url $url, SiButton $siButton, bool $href) {
		$this->id = $id;
		$this->url = $url;
		$this->siButton = $siButton;
	}
	
	function getId(): string {
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::isInputHandled()
	 */
	public function isInputHandled(): bool {
		return $this->inputHandled;
	}
	
	/**
	 * @param bool $inputHandled
	 * @return \rocket\ei\util\control\EiuRefGuiControl
	 */
	public function setInputHandled(bool $inputHandled) {
		$this->inputHandled = $inputHandled;
		return $this;
	}
	
	public function toSiControl(string $controlId): SiControl {
		return new RefSiControl($this->url, $this->siButton);
	}
	
	public function handleEntries(EiFrame $eiFrame, array $eiEntries): SiResult {
		throw new NotYetImplementedException();
	}

	public function handle(EiFrame $eiFrame): SiResult {
		throw new NotYetImplementedException();
	}

	public function handleEntry(EiFrame $eiFrame, EiEntry $eiEntry): SiResult {
		throw new NotYetImplementedException();
	}

	
}