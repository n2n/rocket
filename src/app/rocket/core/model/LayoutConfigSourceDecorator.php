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

use n2n\util\config\Attributes;
use n2n\util\config\source\WritableConfigSource;
use n2n\reflection\property\TypeConstraint;
use n2n\reflection\ArgUtils;
use n2n\util\config\AttributesException;
use n2n\util\config\InvalidConfigurationException;

class LayoutConfigSourceDecorator {
	const START_MENU_ITEM_ID_KEY = 'startLaunchPadId';
	const MENU_GROUPS_KEY = 'menuGroups';
	
	private $writableConfigSource;
	private $attributes;
	
	public function __construct(WritableConfigSource $writableConfigSource) {
		$this->writableConfigSource = $writableConfigSource;
		$this->attributes = new Attributes();
	}
	
	public function load() {
		$this->attributes = new Attributes($this->writableConfigSource->readArray());
	}
	
	public function flush() {
		$this->writableConfigSource->writeArray($this->attributes->toArray());
	}
	
	public function clear() {
		$this->attributes->clear();
	}
	
	public function extractStartLaunchPadId() {
		try {
			return $this->attributes->getString(self::START_MENU_ITEM_ID_KEY);
		} catch (AttributesException $e) {
			throw $this->createDataSourceException($e);
		}
	}
	
	private function createDataSourceException(\Exception $previous): InvalidConfigurationException {
		return new InvalidConfigurationException('Configruation error in data source: ' . $this->writableConfigSource, 
				0, $previous);
	}
	
	public function rawStartLaunchPadId(string $startLaunchPadId = null) {
		$this->attributes->setString(self::START_MENU_ITEM_ID_KEY, $startLaunchPadId);
	}
	
	public function extractMenuGroups(): array {
		$menuGroupsRawData = null;
		try {
			$menuGroupsRawData = $this->attributes->getArray(self::MENU_GROUPS_KEY, false, array(), 
					TypeConstraint::createArrayLike('array', false, TypeConstraint::createSimple('string')));
		} catch (AttributesException $e) {
			throw $this->createDataSourceException($e);
		}
	
		$menuGroupExtractions = array();
		foreach ($menuGroupsRawData as $label => $menuGroupRawData) {
			$menuGroupExtraction = new MenuGroupExtraction($label);
			foreach ($menuGroupRawData as $launchPadId => $label) {
				$menuGroupExtraction->addLaunchPadId($launchPadId, $label);
			}
			$menuGroupExtractions[] = $menuGroupExtraction;
		}
	
		return $menuGroupExtractions;
	}
	
	public function rawMenuGroups(array $menuGroupExtractions) {
		ArgUtils::valArray($menuGroupExtractions, MenuGroupExtraction::class);
		
		$menuGroupsRawData = array();
		foreach ($menuGroupExtractions as $menuGroupExtraction) {
			$label = $menuGroupExtraction->getLabel();
			$menuGroupsRawData[$label] = array();
			foreach ($menuGroupExtraction->getLaunchPadIds() as $launchPadId => $launchPadLabel) {
				$menuGroupsRawData[$label][$launchPadId] = $launchPadLabel;
			}
		}
		
		$this->attributes->set(self::MENU_GROUPS_KEY, $menuGroupsRawData);
	}
}

class MenuGroupExtraction {
	private $label;
	private $launchPadIds = array();
	
	public function __construct(string $label) {
		$this->label = $label;
	}
	
	public function setLabel(string $label) {
		$this->label = $label;
	}
	
	public function getLabel(): string {
		return $this->label;
	}
	
	public function addLaunchPadId(string $launchPadId, string $label = null) {
		$this->launchPadIds[$launchPadId] = $label;
	}
	
	public function setLaunchPadIds(array $launchPadIds) {
		ArgUtils::valArray($launchPadIds, 'string', true);
		$this->launchPadIds = $launchPadIds;	
	}
	
	public function getLaunchPadIds(): array {
		return $this->launchPadIds;
	}
}
