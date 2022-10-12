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
namespace rocket\ei;

use rocket\ei\component\command\EiCmdNature;
use n2n\util\type\ArgUtils;
use rocket\ei\component\command\EiCmd;
use rocket\ei\util\spec\EiuCmd;

class EiCmdPath extends IdPath {
	
	public function __construct(string $id) {
		parent::__construct([$id]);
	}

	public static function from(EiCmd $eiCmd): EiCmdPath {
		return $eiCmd->getEiCmdPath();
	}
	
	public static function create($expression): EiCmdPath {
		if ($expression instanceof EiCmdPath) {
			return $expression;
		}
	
		if ($expression instanceof EiCmd) {
			return $expression->getEiCmdPath();
		}

		if ($expression instanceof EiuCmd) {
			return $expression->getEiCmdPath();
		}

		if (is_string($expression)) {
			return new EiCmdPath($expression);
		}
	
		ArgUtils::valType($expression, ['string', EiCmd::class, EiCmdPath::class, EiuCmd::class], false, 'expression');
	}
}
