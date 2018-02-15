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
namespace rocket\spec\ei\manage\gui;

use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\IdPath;

class GuiIdPath extends IdPath {
	
	public function getShifted() {
		$ids = $this->ids;
		array_shift($ids);
		if (empty($ids)) {
			throw new IllegalStateException();
		}
		return new GuiIdPath($ids);
	}
	
	public function getPoped() {
		$ids = $this->ids;
		array_pop($ids);
		if (empty($ids)) {
			throw new IllegalStateException();
		}
		return new GuiIdPath($ids);
	}
	
	public static function create($expression) {
		if ($expression instanceof GuiIdPath) {
			return $expression;
		}
	
		if (is_array($expression)) {
			return new GuiIdPath($expression);
		}
	
		return new GuiIdPath(explode(self::ID_SEPARATOR, $expression));
	}
	
	public static function createArray(array $expressions) {
		$guiIdPaths = array();
		foreach ($expressions as $key => $expression) {
			$guiIdPaths[$key] = self::create($expression);
		}
		return $guiIdPaths;
	}
}
