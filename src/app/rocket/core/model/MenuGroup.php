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

class MenuGroup {
	private $label;
	private $launchPads = array();
	private $labels = array();
	
	public function __construct(string $label) {
		$this->label = $label;
	}
	
	public function getLabel(): string {
		return $this->label;
	}
	
	/**
	 * @return LaunchPad[] 
	 */
	public function getLaunchPads(): array {
		return $this->launchPads;
	}
	
	public function addLaunchPad(LaunchPad $launchPad, string $label = null) {
		$this->launchPads[$launchPad->getId()] = $launchPad;
		$this->labels[$launchPad->getId()] = $label;
	}
	
	public function containsLaunchPadId($id): bool {
		return isset($this->launchPads[$id]);
	}
	
	public function removeLaunchPadById($id) {
		unset($this->launchPads[$id]);
	}
	
	public function getLaunchPadById($id): LaunchPad {
		if (isset($this->launchPads[$id])) {
			return $this->launchPads[$id];
		}
		
		throw new UnknownLaunchPadException($id);
	}
	
	public function determineLabel(LaunchPad $launchPad) {
		if (isset($this->labels[$launchPad->getId()])) {
			return $this->labels[$launchPad->getId()];
		}
		
		return $launchPad->getLabel();
	}
}
