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

use rocket\ui\si\control\SiButton;
use n2n\util\uri\Url;
use Closure;

class GuiControls {

	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @param Url|string $url
	 * @return RefGuiControl
	 */
	static function ref(string $id, Url|string $url, SiButton $siButton) {
		return new RefGuiControl($id, Url::create($url), $siButton, false);
	}

	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @param Url|string $url
	 * @return RefGuiControl
	 */
	static function href(string $id, Url|string $url, SiButton $siButton) {
		return new RefGuiControl($id, Url::create($url), $siButton, true);
	}

	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @param Closure $callback
	 * @return CallbackGuiControl
	 */
	static function callback(string $id, SiButton $siButton, Closure $callback) {
		return new CallbackGuiControl($id, $callback, $siButton);
	}

	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @return GroupGuiControl
	 */
	static function group(string $id, SiButton $siButton) {
		return new GroupGuiControl($id, $siButton);
	}

	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @return DeactivatedGuiControl
	 */
	static function deactivated(string $id, SiButton $siButton) {
		return new DeactivatedGuiControl($id, $siButton);
	}
}