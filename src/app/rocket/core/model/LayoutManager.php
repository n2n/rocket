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

use rocket\spec\Spec;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\InvalidMenuConfigurationException;

class LayoutManager {
	private $scsd;
	private $spec;
	
	private $startLaunchPadLoaded = false;
	private $startLaunchPad;
	private $menuGroups;
	
	public function __construct(LayoutConfigSourceDecorator $scsd, Spec $spec) {
		$this->scsd = $scsd;
		$this->spec = $spec;
	}
	
	public function reset() {
		$this->startLaunchPad = null;
		$this->menuGroups = null;
	}
	
	public function getStartLaunchPad() {
		if ($this->startLaunchPadLoaded) {
			return $this->startLaunchPad;
		}
		
		if (null !== ($startLaunchPadId = $this->scsd->extractStartLaunchPadId())) {
			try {
				$this->startLaunchPad = $this->spec->getLaunchPadById($startLaunchPadId);
			} catch (UnknownLaunchPadException $e) {
				throw new InvalidMenuConfigurationException('Failed to initialize start LaunchPad.', 0, $e);
			}
		}
		
		$this->startLaunchPadLoaded = true;
		return $this->startLaunchPad;
	}
	
	public function setStartLaunchPad(LaunchPad $startLaunchPad = null) {
		$this->startLaunchPad = $startLaunchPad;
	}
	
	public function getMenuGroups() {
		if ($this->menuGroups !== null) {
			return $this->menuGroups;
		}
		
		$this->menuGroups = array();
		foreach ($this->scsd->extractMenuGroups() as $menuGroupExtraction) {
			$menuGroup = new MenuGroup($menuGroupExtraction->getLabel());
				
			try {
				foreach ($menuGroupExtraction->getLaunchPadIds() as $launchPadId => $label) {
					$menuGroup->addLaunchPad($this->spec->getLaunchPadById($launchPadId), $label);
				}
			} catch (UnknownLaunchPadException $e) {
				throw new InvalidMenuConfigurationException('Failed to initialize MenuGroup: '
						. $menuGroupExtraction->getLabel(), 0, $e);
			}
				
			$this->menuGroups[] = $menuGroup;
		}
		return $this->menuGroups;
	}
	
	public function setMenuGroups(array $menuGroups) {
		$this->menuGroups = $menuGroups;
	}
	
	public function flush() {
		throw new NotYetImplementedException();
		
// 		if ($this->startLaunchPadLoaded) {
// 			$this->scsd->rawStartLaunchPadId($bla);
// 		}
	}
}
