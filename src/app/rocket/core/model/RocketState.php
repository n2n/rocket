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

use n2n\context\RequestScoped;
use n2n\util\col\GenericArrayObject;
use rocket\core\model\launch\LaunchPad;

class RocketState implements RequestScoped {
	private $breadcrumbs = array();
	private $activeLaunchPad;
	
	public function __construct() {
		$this->breadcrumbs = new GenericArrayObject(null, 'rocket\core\model\Breadcrumb');
	}

	/**
	 * @return \rocket\core\model\Breadcrumb
	 */
	public function getBreadcrumbs() {
		return $this->breadcrumbs;
	}
	
	public function addBreadcrumb(Breadcrumb $breadcrumb) {
		$this->breadcrumbs[] = $breadcrumb;
	}
	
	/**
	 * @param LaunchPad $activeLaunchPad
	 */
	public function setActiveLaunchPad(LaunchPad $activeLaunchPad = null) {
		$this->activeLaunchPad = $activeLaunchPad;
	}
	
	/**
	 * @return LaunchPad
	 */
	public function getActiveLaunchPad() {
		return $this->activeLaunchPad;
	}
}
