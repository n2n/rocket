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
use n2n\reflection\ArgUtils;
use rocket\ei\EiPropPath;

class GuiPropPath {
	const EI_PROP_PATH_SEPARATOR = ',';
	
	/**
	 * @var EiPropPath[]
	 */
	protected $eiPropPaths = array();
	
	/**
	 * @param EiPropPath[] $eiPropPaths
	 */
	public function __construct(array $eiPropPaths) {
		ArgUtils::valArray($eiPropPaths, EiPropPath::class);
		$this->eiPropPaths = $eiPropPaths;
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
		
		throw new IllegalStateException('GuiPropPath is empty.');
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
	 * @return \rocket\ei\manage\gui\GuiPropPath
	 */
	public function getShifted() {
		$eiPropPaths = $this->eiPropPaths;
		array_shift($eiPropPaths);
		if (empty($eiPropPaths)) {
			throw new IllegalStateException();
		}
		return new GuiPropPath($eiPropPaths);
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\manage\gui\GuiPropPath
	 */
	public function getPoped() {
		$eiPropPaths = $this->eiPropPaths;
		array_pop($eiPropPaths);
		if (empty($eiPropPaths)) {
			throw new IllegalStateException();
		}
		return new GuiPropPath($eiPropPaths);
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
	
	/**
	 * @param mixed $expression
	 * @return \rocket\ei\manage\gui\GuiPropPath
	 */
	public static function create($expression) {
		if ($expression instanceof GuiPropPath) {
			return $expression;
		}
	
		$parts = null;
		if (is_array($expression)) {
			$parts = $expression;
		} else {
			$parts = explode(self::EI_PROP_PATH_SEPARATOR, (string) $expression);
		}
		
		$guiPropPath = new GuiPropPath([]);
		$guiPropPath->eiPropPaths = [];
		foreach ($parts as $part) {
			$guiPropPath->eiPropPaths[] = EiPropPath::create($part);
		}
		return $guiPropPath;
	}
	
	public static function createArray(array $expressions) {
		$eiPropPaths = array();
		foreach ($expressions as $key => $expression) {
			$eiPropPaths[$key] = self::create($expression);
		}
		return $eiPropPaths;
	}
}
