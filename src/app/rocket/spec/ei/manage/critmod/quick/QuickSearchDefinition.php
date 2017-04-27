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
namespace rocket\spec\ei\manage\critmod\quick;

use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\critmod\quick\QuickSearchField;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraintGroup;

class QuickSearchDefinition {
	private $quickSearchFields = array();
	
	public function putQuickSearchField(EiFieldPath $eiFieldPath, QuickSearchField $quickSearchField) {
		$this->quickSearchFields[(string) $eiFieldPath] = $quickSearchField;	
	}
	
	public function getQuickSearchFields(): array {
		return $this->quickSearchFields;
	}
	
	private function filterFields(array $eiFieldPaths) {
		$quickSearchFields = array();
		foreach ($eiFieldPaths as $eiFieldPath) {
			$eiFieldPathStr = (string) $eiFieldPath;
			if (isset($this->quickSearchFields[$eiFieldPathStr])) {
				$quickSearchFields[] = $this->quickSearchFields[$eiFieldPathStr];
			}
		}
		return $quickSearchFields;
	}
	
	public function buildCriteriaConstraint(string $searchStr, array $eiFieldPaths = null) {
		$quickSearchFields = null;
		if ($eiFieldPaths === null) {
			$quickSearchFields = $this->quickSearchFields;
		} else {
			ArgUtils::valArray($eiFieldPaths, EiFieldPath::class);
			$quickSearchFields = $this->filterFields($eiFieldPaths);
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
