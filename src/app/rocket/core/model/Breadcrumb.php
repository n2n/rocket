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
namespace rocket\core\model;

use rocket\si\meta\SiBreadcrumb;

class Breadcrumb {
	private $navPoint;
	private $label;
	
	/**
	 * @param NavPoint $navPoint
	 * @param string $label
	 * @param bool $ref
	 */
	public function __construct(NavPoint $navPoint, string $label) {
		$this->navPoint = $navPoint;
		$this->label = $label;
	}
	
	/**
	 * @return \rocket\core\model\NavPoint
	 */
	public function getNavPoint() {
		return $this->navPoint;
	}
	
	/**
	 * @param NavPoint $navPoint
	 * @return Breadcrumb
	 */
	public function setNavPoint(NavPoint $navPoint) {
		$this->navPoint = $navPoint;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @param string $label
	 * @return Breadcrumb
	 */
	public function setLabel(string $label) {
		$this->label = $label;
		return $this;
	}
	
	function toSiBreadcrumb() {
		return new SiBreadcrumb($this->navPoint->isSiref(), $this->navPoint->getUrl(), 
				$this->label);
	}
}
