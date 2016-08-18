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
use rocket\spec\ei\component\field\EiField;

class EiFieldPath extends IdPath {

	public function pushed($id): EiFieldPath {
		$ids = $this->ids;
		$ids[] = $id;
		return new EiFieldPath($ids);
	}	
	
	public function poped(): EiFieldPath {
		$ids = $this->ids;
		array_pop($ids);
		return new EiFieldPath($ids);
	}
	
	public static function from(EiField $eiField): EiFieldPath {
		$ids = array();
		do {
			$ids[] = $eiField->getId();
		} while (null !== ($eiField = $eiField->getParentEiField()));
	
		rsort($ids);
		return new EiFieldPath($ids);
	}
	
	public static function create($expression): EiFieldPath {
		if ($expression instanceof EiFieldPath) {
			return $expression;
		}
		
		if ($expression instanceof EiField) {
			return self::from($expression);
		}
	
		if (is_array($expression)) {
			return new EiFieldPath($expression);
		}
	
		return new EiFieldPath(explode(self::ID_SEPARATOR, $expression));
	}
	
	public function ext(...$args): EiFieldPath {
		return new EiFieldPath(array_merge($this->argsToIds($args), $args));
	}
}
