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
namespace rocket\ei\manage\critmod\quick;

use n2n\util\type\ArgUtils;
use rocket\ei\EiPropPath;
use rocket\ei\manage\critmod\filter\ComparatorConstraintGroup;

class QuickSearchDefinition {
	private $quickSearchFields = array();
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param QuickSearchProp $quickSearchField
	 */
	public function putQuickSearchProp(EiPropPath $eiPropPath, QuickSearchProp $quickSearchField) {
		$this->quickSearchFields[(string) $eiPropPath] = $quickSearchField;	
	}
	
	/**
	 * @return QuickSearchProp[]
	 */
	public function getQuickSearchProps(): array {
		return $this->quickSearchFields;
	}
	
	/**
	 * @param EiPropPath[] $eiPropPaths
	 * @return QuickSearchProp[]
	 */
	private function filterProps(array $eiPropPaths) {
		$quickSearchFields = array();
		foreach ($eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			if (isset($this->quickSearchFields[$eiPropPathStr])) {
				$quickSearchFields[] = $this->quickSearchFields[$eiPropPathStr];
			}
		}
		return $quickSearchFields;
	}
	
	/**
	 * 
	 * @param string $searchStr
	 * @param EiPropPath[] $eiPropPaths
	 * @return null|\rocket\ei\manage\critmod\filter\ComparatorConstraintGroup
	 */
	public function buildCriteriaConstraint(string $searchStr, array $eiPropPaths = null) {
		$quickSearchFields = null;
		if ($eiPropPaths === null) {
			$quickSearchFields = $this->quickSearchFields;
		} else {
			ArgUtils::valArray($eiPropPaths, EiPropPath::class);
			$quickSearchFields = $this->filterProps($eiPropPaths);
		} 
		
		if (empty($quickSearchFields)) return null;
		
		$comparatorConstraintGroup = new ComparatorConstraintGroup(true);
		
		foreach (preg_split('/\s+/', $searchStr) as $searchStrPart) {
			$queryStr = trim($searchStrPart);
			$queryComparatorConstraintGroup = new ComparatorConstraintGroup(false);
			
			foreach ($quickSearchFields as $quickSearchField) {
				$queryComparatorConstraintGroup->addComparatorConstraint(
						$quickSearchField->createComparatorConstraint($searchStrPart));
			}
			
			$comparatorConstraintGroup->addComparatorConstraint($queryComparatorConstraintGroup);
		}
		
		if ($comparatorConstraintGroup->isEmpty()) {
			return null;
		}
				
		return $comparatorConstraintGroup;
	}
}
