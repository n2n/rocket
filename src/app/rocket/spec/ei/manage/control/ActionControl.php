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
use n2n\util\uri\Url;
use rocket\spec\ei\manage\control\Control;

class ActionControl implements Control {
	private $url;
	private $controlButton;
	
	public function __construct($url, ControlButton $controlButton) {
		$this->url = $url;
		$this->controlButton = $controlButton;
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function getControlButton(): ControlButton {
		return $this->controlButton;
	}
	
	public function createUiComponent(bool $iconOnly): UiComponent {
		return $this->controlButton->toButton($iconOnly, array('href' => $this->url, 'class' => 'rocket-action'));
	}
}
