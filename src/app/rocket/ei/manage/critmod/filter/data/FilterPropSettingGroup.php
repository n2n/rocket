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
namespace rocket\ei\manage\critmod\filter\data;

use n2n\util\col\GenericArrayObject;
use n2n\util\config\Attributes;
use n2n\reflection\property\TypeConstraint;

class FilterPropSettingGroup {
	const ATTR_USE_AND_KEY = 'useAnd';
	const ATTR_FILTER_ITEMS_KEY = 'items';
	const ATTR_FILTER_GROUPS_KEY = 'groups';

	private $filterPropSettings = array();
	private $filterPropSettingGroups = array();
	private $andUsed = true;

	public function __construct() {
		$this->filterPropSettings = new GenericArrayObject(null, FilterPropSetting::class);
		$this->filterPropSettingGroups = new GenericArrayObject(null, FilterPropSettingGroup::class);
	}
	
	public function getFilterPropSettings(): \ArrayObject {
		return $this->filterPropSettings;
	}
	
	public function getFilterPropSettingGroups(): \ArrayObject {
		return $this->filterPropSettingGroups;
	}

	public function isEmpty() {
		return empty($this->filterPropSettings) && empty($this->filterPropSettingGroups);
	}
	
	public function isAndUsed(): bool {
		return $this->andUsed;
	}

	public function setAndUsed(bool $andUsed) {
		$this->andUsed = $andUsed;
	}
	
	public function toAttrs(): array {
		$filterItemsAttrs = array();
		foreach ($this->filterPropSettings as $filterItemData) {
			$filterItemsAttrs[] = $filterItemData->toAttrs();
		}
		
		$filterGroupsAttrs = array();
		foreach ($this->filterPropSettingGroups as $filterGroupData) {
			$filterGroupsAttrs[] = $filterGroupData->toAttrs();
		}

		return array(
				self::ATTR_USE_AND_KEY => $this->andUsed,
				self::ATTR_FILTER_ITEMS_KEY => $filterItemsAttrs,
				self::ATTR_FILTER_GROUPS_KEY => $filterGroupsAttrs);
	}

	public static function create(Attributes $attributes): FilterPropSettingGroup {
		$fgd = new FilterPropSettingGroup();
		$fgd->setAndUsed($attributes->getBool(self::ATTR_USE_AND_KEY, false, true));

		$settings = $fgd->getFilterPropSettings();
		foreach ($attributes->getArray(self::ATTR_FILTER_ITEMS_KEY, false, array(), 
				TypeConstraint::createArrayLike('array')) as $filterItemAttrs) {
			$settings->append(FilterPropSetting::create(new Attributes($filterItemAttrs)));
		}
		
		$settingGroups = $fgd->getFilterPropSettingGroups();
		foreach ($attributes->getArray(self::ATTR_FILTER_GROUPS_KEY, false, array(), 
				TypeConstraint::createArrayLike('array')) as $filterGroupAttrs) {
			$settingGroups->append(FilterPropSettingGroup::create(new Attributes($filterGroupAttrs)));
		}

		return $fgd;
	}
}
