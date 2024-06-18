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
namespace rocket\op\ei\util\control;

use rocket\ui\si\control\SiButton;
use rocket\op\ei\util\EiuAnalyst;
use n2n\util\uri\Url;
use rocket\impl\ei\manage\gui\RefGuiControl;
use rocket\impl\ei\manage\gui\CallbackGuiControl;
use rocket\impl\ei\manage\gui\GroupGuiControl;
use rocket\impl\ei\manage\gui\DeactivatedGuiControl;
use rocket\impl\ei\manage\gui\GuiControls;

class EiuGuiControlFactory {
	private $eiuAnalyst;
	
	public function __construct(EiuAnalyst $eiuAnalyst) {
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @param mixed|null $urlExt
	 * @return RefGuiControl
	 */
	public function newCmdRef(string $id, SiButton $siButton, $urlExt = null) {
		return GuiControls::ref($id, $this->eiuAnalyst->getEiuFrame(true)->getCmdUrl()->ext($urlExt), $siButton);
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @param mixed|null $urlExt
	 * @return RefGuiControl
	 */
	public function newCmdHref(string $id, SiButton $siButton, Url $urlExt = null): RefGuiControl {
		return GuiControls::href($id, $this->eiuAnalyst->getEiuFrame(true)->getCmdUrl()->ext($urlExt), $siButton);
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @param \Closure $callback
	 * @return CallbackGuiControl
	 */
	public function newCallback(SiButton $siButton, \Closure $callback): CallbackGuiControl {
		return GuiControls::callback($siButton, $callback);
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @return GroupGuiControl
	 */
	public function newGroup(string $id, SiButton $siButton) {
		return GuiControls::group($id, $siButton);
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @return DeactivatedGuiControl
	 */
	public function newDeactivated(string $id, SiButton $siButton) {
		return new DeactivatedGuiControl($id, $siButton);
	}
}