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
use rocket\ui\gui\control\GuiControl;

class GuiControls {

	/**
	 * @param Url|string $url
	 * @param SiButton $siButton
	 * @return RefGuiControl
	 */
	static function ref(Url|string $url, SiButton $siButton): RefGuiControl {
		return new RefGuiControl(Url::create($url), $siButton, false);
	}

	/**
	 * @param Url|string $url
	 * @param SiButton $siButton
	 * @return RefGuiControl
	 */
	static function href(Url|string $url, SiButton $siButton): RefGuiControl {
		return new RefGuiControl(Url::create($url), $siButton, true);
	}

	/**
	 * @param SiButton $siButton
	 * @param Closure $callback
	 * @return CallbackGuiControl
	 */
	static function callback(SiButton $siButton, Closure $callback): CallbackGuiControl {
		return new CallbackGuiControl($callback, $siButton);
	}

	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @return GroupGuiControl
	 */
	static function group(SiButton $siButton): GroupGuiControl {
		return new GroupGuiControl($siButton);
	}

	/**
	 * @param SiButton $siButton
	 * @return DeactivatedGuiControl
	 */
	static function deactivated(SiButton $siButton): DeactivatedGuiControl {
		return new DeactivatedGuiControl($siButton);
	}
}