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
namespace rocket\op\ei\manage\gui\factory;

use rocket\op\ei\manage\EiObject;
use rocket\ui\si\content\SiEntryIdentifier;
use rocket\op\ei\manage\entry\EiEntry;

class EiSiEntryIdentifierFactory {
	static function determineEntryId(EiObject $eiObject): ?string {
		$eiEntityObj = $eiObject->getEiEntityObj();
		return $eiEntityObj->hasId() ? $eiEntityObj->getPid() : null;
	}

	static function create(EiEntry $eiEntry, int $viewMode): SiEntryIdentifier {
		return new SiEntryIdentifier(
				EiSiMaskIdentifierFactory::create($eiEntry->getEiMask(), $viewMode),
				self::determineEntryId($eiEntry->getEiObject()));
	}
}