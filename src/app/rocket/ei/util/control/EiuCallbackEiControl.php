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

use rocket\ei\component\command\EiControl;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\frame\EiFrame;
use rocket\si\control\SiControl;
use rocket\si\control\SiResult;

class EiuCallbackEiControl implements EiControl {
	
	public function toSiControl(string $controlId): SiControl {
		return new ApiCallSiControl($controlId);
	}

	public function handle(EiFrame $eiFrame): SiResult {
	}

	public function handleEntry(EiFrame $eiFrame, EiEntry $eiEntry): SiResult {
	}
	
	public function handleEntries(EiFrame $eiFrame, array $eiEntries): SiResult {
	}

	
}