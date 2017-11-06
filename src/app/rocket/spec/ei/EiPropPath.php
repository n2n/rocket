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
namespace rocket\spec\ei;

use rocket\spec\ei\IdPath;
use rocket\spec\ei\component\field\EiProp;

class EiPropPath extends IdPath {

	public function pushed($id): EiPropPath {
		$ids = $this->ids;
		$ids[] = $id;
		return new EiPropPath($ids);
	}	
	
	public function poped(): EiPropPath {
		$ids = $this->ids;
		array_pop($ids);
		return new EiPropPath($ids);
	}
	
	public static function from(EiProp $eiProp): EiPropPath {
		$ids = array();
		do {
			$ids[] = $eiProp->getId();
		} while (null !== ($eiProp = $eiProp->getParentEiProp()));
	
		rsort($ids);
		return new EiPropPath($ids);
	}
	
	public static function create($expression): EiPropPath {
		if ($expression instanceof EiPropPath) {
			return $expression;
		}
		
		if ($expression instanceof EiProp) {
			return self::from($expression);
		}
	
		if (is_array($expression)) {
			return new EiPropPath($expression);
		}
	
		return new EiPropPath(explode(self::ID_SEPARATOR, $expression));
	}
	
	public function ext(...$args): EiPropPath {
		return new EiPropPath(array_merge($this->argsToIds($args), $args));
	}
}
