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

use rocket\ei\component\command\EiCommand;
use rocket\si\control\SiButton;
use rocket\ei\EiCommandPath;
use rocket\ei\util\frame\EiuFrame;

class EiuControlFactory {
	private $eiuFrame;
	private $eiCommand;
	
	public function __construct(EiuFrame $eiuFrame, EiCommand $eiCommand) {
		$this->eiuFrame = $eiuFrame;
		$this->eiCommand = $eiCommand;
	}
	
	/**
	 * @param mixed $urlExt
	 * @return \n2n\util\uri\Url
	 */
	private function createCmdUrl($urlExt) {
		return $this->eiuFrame->getHttpContext()
				->getControllerContextPath($this->eiuFrame->getEiFrame()->getControllerContext())
				->ext((string) EiCommandPath::from($this->eiCommand))->toUrl()->ext($urlExt);
	}
	
	public function createCmdRef(SiButton $siButton, $urlExt = null) {
		return new EiuRefEiControl($this->createCmdUrl($urlExt), $siButton, false);
	}
	
	public function createCmdHref(SiButton $siButton, $urlExt = null) {
		return new EiuRefEiControl($this->createCmdUrl($urlExt), $siButton, true);
	}
	
	public function createCallback(SiButton $siButton, \Closure $callback) {
		return new EiuCallbackEiControl($callback, $siButton);
	}
	
// 	public function createGroup(ControlButton $siButton): GroupControl {
// 		return new GroupControl($siButton);
// 	}
	
// 	public function createDeactivated(ControlButton $siButton) {
// 		return new DeactivatedControl($siButton);
// 	}
}