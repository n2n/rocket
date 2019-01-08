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
namespace rocket\ei\manage\gui;

use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use rocket\ei\EiPropPath;
use rocket\ei\component\prop\EiProp;
use n2n\util\col\Hashable;
use n2n\util\type\TypeUtils;

class GuiFieldPath implements Hashable {
	const EI_PROP_PATH_SEPARATOR = '.';
	
	/**
	 * @var EiPropPath[]
	 */
	protected $eiPropPaths = array();
	
	/**
	 * @param EiPropPath[] $eiPropPaths
	 */
	public function __construct(array $eiPropPaths) {
		ArgUtils::valArray($eiPropPaths, EiPropPath::class);
		$this->eiPropPaths = array_values($eiPropPaths);
	}
	
	/**
	 * @return int
	 */
	public function size() {
		return count($this->eiPropPaths);
	}
	
	public function isEmpty() {
		return empty($this->eiPropPaths);
	}
	
	protected function ensureNotEmpty() {
		if (!$this->isEmpty()) return;
		
		throw new IllegalStateException('GuiFieldPath is empty.');
	}
	
	/**
	 * @return boolean
	 */
	public function hasMultipleEiPropPaths() {
		return count($this->eiPropPaths) > 1;
	}
	
	/**
	 * @return EiPropPath
	 */
	public function getFirstEiPropPath() {
		$this->ensureNotEmpty();
		return reset($this->eiPropPaths);
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\manage\gui\GuiFieldPath
	 */
	public function getShifted() {
		$eiPropPaths = $this->eiPropPaths;
		array_shift($eiPropPaths);
		if (empty($eiPropPaths)) {
			throw new IllegalStateException();
		}
		return new GuiFieldPath($eiPropPaths);
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\manage\gui\GuiFieldPath
	 */
	public function getPoped() {
		$eiPropPaths = $this->eiPropPaths;
		array_pop($eiPropPaths);
		if (empty($eiPropPaths)) {
			throw new IllegalStateException();
		}
		return new GuiFieldPath($eiPropPaths);
	}
	
	public function startsWith(GuiFieldPath $guiFieldPath, bool $checkOnEiPropPathLevel) {
		$size = $this->size();
		
		if ($guiFieldPath->size() > $size) {
			return false;
		}
		
		foreach ($guiFieldPath->eiPropPaths as $key => $eiPropPath) {
			if (!isset($this->eiPropPaths[$key])) return false;
			
			if ($this->eiPropPaths[$key]->equals($eiPropPath)) {
				continue;
			}
			
			return $checkOnEiPropPathLevel && $key + 1 == $size && $this->eiPropPaths[$key]->startsWith($eiPropPath);
		}
		
		return true;
	}
	
	public function equals($guiFieldPath) {
		if (!($guiFieldPath instanceof GuiFieldPath) || $guiFieldPath->size() != $this->size()) {
			return false;
		}
		
		foreach ($guiFieldPath->eiPropPaths as $key => $eiPropPath) {
			if (!$eiPropPath->equals($this->eiPropPaths[$key])) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * @return EiPropPath[]
	 */
	public function toArray() {
		return $this->eiPropPaths;
	}
	
	public function __toString() {
		return implode(self::EI_PROP_PATH_SEPARATOR, $this->eiPropPaths);
	}
	
	public function hashCode(): string {
		return $this->__toString();
	}
	
	/**
	 * @param mixed $expression
	 * @return \rocket\ei\manage\gui\GuiFieldPath
	 */
	public static function create($expression) {
		if ($expression instanceof GuiFieldPath) {
			return $expression;
		}
	
		$parts = null;
		if (is_array($expression)) {
			$parts = $expression;
		} else if ($expression instanceof EiProp) {
			return new GuiFieldPath([EiPropPath::from($expression)]);
		} else if ($expression instanceof EiPropPath) {
			return new GuiFieldPath([$expression]);
		} else if (is_scalar($expression)) {
			$parts = explode(self::EI_PROP_PATH_SEPARATOR, (string) $expression);
		} else if ($expression === null) {
			$parts = [];
		} else {
			throw new \InvalidArgumentException('Passed value type can not be converted to a GuiFieldPath: ' 
					. TypeUtils::getTypeInfo($expression));
		}
		
		$guiFieldPath = new GuiFieldPath([]);
		$guiFieldPath->eiPropPaths = [];
		foreach ($parts as $part) {
			$guiFieldPath->eiPropPaths[] = EiPropPath::create($part);
		}
		return $guiFieldPath;
	}
	
	public static function createArray(array $expressions) {
		$eiPropPaths = array();
		foreach ($expressions as $key => $expression) {
			$eiPropPaths[$key] = self::create($expression);
		}
		return $eiPropPaths;
	}
}
