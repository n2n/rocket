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
namespace rocket\ei\manage\security\filter;

use n2n\util\config\AttributesException;
use rocket\ei\EiPropPath;

class SecurityFilterDefinition {
	/**
	 * @var SecurityFilterProp[] $props
	 */
	private $props = array();
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param SecurityFilterProp $securityFilterProp
	 */
	public function putProp(EiPropPath $eiPropPath, SecurityFilterProp $securityFilterProp) {
		$this->props[(string) $eiPropPath] = $securityFilterProp;
	}
	
	/**
	 * @return \rocket\ei\manage\security\filter\SecurityFilterProp[]
	 */
	public function getProps() {
		return $this->props;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws UnknownSecurityFilterPropException
	 * @return \rocket\ei\manage\security\filter\SecurityFilterProp
	 */
	public function getFilterPropById(EiPropPath $eiPropPath) {
		if (isset($this->props[(string) $eiPropPath])) {
			return $this->props[(string) $eiPropPath];
		}
		
		throw new UnknownSecurityFilterPropException();
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return bool
	 */
	public function containsProp(EiPropPath $eiPropPath) {
		return isset($this->props[(string) $eiPropPath]);
	}
	
	/**
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->props);
	}
	
	public function createComparatorConstraint(FilterPropSettingGroup $filterPropSettingGroup): ComparatorConstraint {
		$criteriaComparators = array();
		
		foreach ($filterPropSettingGroup->getFilterPropSettings() as $subFilterPropSetting) {
			$id = $subFilterPropSetting->getFilterPropId();
			if (!isset($this->props[$id])) {
				continue;
			}
			
			try {
				$criteriaComparators[] = $this->props[$id]->createComparatorConstraint(
						$subFilterPropSetting->getAttributes());
			} catch (AttributesException $e) {}
		}
		
		foreach ($filterPropSettingGroup->getFilterPropSettingGroups() as $subFilterPropSettingGroup) {
			$criteriaComparators[] = $this->createComparatorConstraint($subFilterPropSettingGroup);
		}
		
		return new ComparatorConstraintGroup($filterPropSettingGroup->isAndUsed(), $criteriaComparators);
	}
	
	// 	private function createElementComparatorConstraint(FilterDataElement $element) {
	// 		if ($element instanceof FilterDataUsage) {
	// 			$itemId = $element->getItemId();
	// 			if (isset($this->filterProps[$itemId])) {
	// 				$comparatorConstraint = $this->filterProps[$itemId]->createComparatorConstraint($element->getAttributes());
	// 				ArgUtils::valTypeReturn($comparatorConstraint,
	// 						'rocket\ei\manage\critmod\ComparatorConstraint',
	// 						$this->filterProps[$itemId], 'createComparatorConstraint');
	// 				return $comparatorConstraint;
	// 			}
	// 		} else if ($element instanceof FilterDataGroup) {
	// 			$group = new CriteriaComparatorConstraintGroup($element->isAndUsed());
	// 			foreach ($element->getAll() as $childElement) {
	// 				$group->addComparatorConstraint($this->createElementComparatorConstraint($childElement));
	// 			}
	// 			return $group;
	// 		}
	
	// 		return null;
	// 	}
	
	
	// 	public static function createFromFilterProps(FilterData $filterData, array $filterItems) {
	// 		$filterModel = new FilterModel($filterData);
	// 		foreach ($filterItems as $id => $filterItem) {
	// 			$filterModel->putFilterProp($id, $filterItem);
	// 		}
	// 		return $filterModel;
	// 	}
}

class UnknownSecurityFilterPropException extends \RuntimeException {
	
}