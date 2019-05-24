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
use rocket\ei\manage\gui\control\GeneralGuiControl;
use rocket\ei\manage\gui\control\EntryGuiControl;
use rocket\ei\manage\gui\control\SelectionGuiControl;
use rocket\si\control\impl\ApiCallSiControl;
use rocket\si\control\SiButton;
use rocket\ei\manage\gui\control\GuiControlPath;

class EiuCallbackGuiControl implements GeneralGuiControl, EntryGuiControl, SelectionGuiControl {
	private $id;
	private $viewMode;
	private $callback;
	private $siButton;
	private $inputHandled = false;
	
	/**
	 * @param string $id
	 * @param \Closure $callback
	 * @param SiButton $siButton
	 */
	function __construct(string $id, int $viewMode, \Closure $callback, SiButton $siButton) {
		$this->id = $id;
		$this->viewMode = $viewMode;
		$this->callback = $callback;
		$this->siButton = $siButton;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::getId()
	 */
	function getId(): string {
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::isInputHandled()
	 */
	function isInputHandled(): bool {
		return $this->inputHandled;
	}
	
	/**
	 * @param bool $inputHandled
	 * @return \rocket\ei\util\control\EiuCallbackGuiControl
	 */
	function setInputHandled(bool $inputHandled) {
		$this->inputHandled = $inputHandled;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::toSiControl()
	 */
	function toSiControl(GuiControlPath $guiControlPath): SiControl {
		return new ApiCallSiControl(new SiApiCallId($guiControlPath, $this->viewMode), 
				$this->siButton, $this->inputHandled);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GeneralGuiControl::handle()
	 */
	function handle(EiFrame $eiFrame): SiResult {
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\EntryGuiControl::handleEntry()
	 */
	function handleEntry(EiFrame $eiFrame, EiEntry $eiEntry): SiResult {
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\SelectionGuiControl::handleEntries()
	 */
	function handleEntries(EiFrame $eiFrame, array $eiEntries): SiResult {
	}
}
