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
namespace rocket\spec\ei\manage\control;

use n2n\web\ui\UiComponent;
use rocket\spec\ei\manage\EiFrame;
use n2n\util\uri\Url;
use rocket\spec\ei\component\command\EiCommand;
use rocket\spec\ei\manage\control\Control;

class HrefControl implements Control {
	private $href;
	private $controlButton;
	
	public function __construct($href, ControlButton $controlButton) {
		$this->href = $href;
		$this->controlButton = $controlButton;
	}
	
	public function getHref() {
		return $this->href;
	}
	
	public function getControlButton(): ControlButton {
		return $this->controlButton;
	}
	
	public function createUiComponent(bool $iconOnly): UiComponent {
		return $this->controlButton->toButton($iconOnly, array('href' => $this->href));
	}
	
	public static function create(EiFrame $eiFrame, EiCommand $eiCommand, Url $urlExt = null, 
			ControlButton $controlButton) {
		return new HrefControl(
				$eiFrame->getN2nContext()->getHttpContext()->getControllerContextPath($eiFrame->getControllerContext())
						->ext($eiCommand->getId())->toUrl()->ext($urlExt), 
				$controlButton);
	}
}
