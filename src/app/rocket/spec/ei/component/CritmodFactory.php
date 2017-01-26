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
namespace rocket\spec\ei\component;

use rocket\spec\ei\component\field\SortableEiField;
use rocket\spec\ei\manage\critmod\SortModel;
use rocket\spec\ei\component\field\EiFieldCollection;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\critmod\filter\FilterDefinition;
use rocket\spec\ei\component\field\FilterableEiField;
use n2n\core\container\N2nContext;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\critmod\filter\EiMappingFilterDefinition;
use rocket\spec\ei\manage\critmod\sort\SortDefinition;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\critmod\sort\SortField;
use rocket\spec\ei\component\field\SortableEiFieldFork;
use rocket\spec\ei\manage\critmod\sort\SortFieldFork;
use rocket\spec\ei\manage\critmod\filter\EiMappingFilterField;
use rocket\spec\ei\manage\critmod\filter\FilterField;
use rocket\spec\ei\component\field\QuickSearchableEiField;
use rocket\spec\ei\manage\critmod\quick\QuickSearchDefinition;

class CritmodFactory {
	private $eiFieldCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiFieldCollection $eiFieldCollection, EiModificatorCollection $eiModificatorCollection) {
		$this->eiFieldCollection = $eiFieldCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}
	
// 	public static function createFilterModel(EiSpec $eiSpec, N2nContext $n2nContext) {
// 		return self::createFilterModelInstance($eiSpec, $n2nContext);
// 	}
	
// 	public static function createFilterModelFromEiState(EiState $eiState) {
// 		return self::createFilterModelInstance($eiState->getContextEiMask()->getEiEngine()->getEiSpec(), 
// 				$eiState->getN2nContext(), $eiState);
// 	}
	
	public function createManagedFilterDefinition(EiState $eiState): FilterDefinition {
		$filterDefinition = new FilterDefinition();
		foreach ($this->eiFieldCollection as $id => $eiField) {
			if (!($eiField instanceof FilterableEiField)) continue;
			
			$filterField = $eiField->buildManagedFilterField($eiState);
			ArgUtils::valTypeReturn($filterField, FilterField::class, $eiField, 'buildManagedFilterField', true);
			
			if ($filterField !== null) {
				$filterDefinition->putFilterField($eiField->getId(), $filterField);
			}
		}		
		return $filterDefinition;
	}
	
	public function createFilterDefinition(N2nContext $n2nContext): FilterDefinition {
		$filterDefinition = new FilterDefinition();
		foreach ($this->eiFieldCollection as $id => $eiField) {
			if (!($eiField instanceof FilterableEiField)) continue;
			
			$filterField = $eiField->buildFilterField($n2nContext);
			ArgUtils::valTypeReturn($filterField, FilterField::class, $eiField, 'buildFilterField', true);
			
			if ($filterField !== null) {
				$filterDefinition->putFilterField($eiField->getId(), $filterField);
			}
		}
		return $filterDefinition;
	}
	
	public function createEiMappingFilterDefinition(N2nContext $n2nContext): EiMappingFilterDefinition {
		$mappableFilterDefinition = new EiMappingFilterDefinition();
		
		foreach ($this->eiFieldCollection as $id => $eiField) {
			if (!($eiField instanceof FilterableEiField)) continue;
			
			$eiMappingFilterField = $eiField->buildEiMappingFilterField($n2nContext);
			ArgUtils::valTypeReturn($eiMappingFilterField, EiMappingFilterField::class, $eiField, 
					'buildEiMappingFilterField', true);

			if ($eiMappingFilterField !== null) {
				$mappableFilterDefinition->putEiMappingFilterField(EiFieldPath::from($eiField), $eiMappingFilterField);
			}
		}
		
		return $mappableFilterDefinition;
	}
	
	public function createManagedSortDefinition(EiState $eiState): SortDefinition {
		$sortDefinition = new SortDefinition();
		
		foreach ($this->eiFieldCollection as $id => $eiField) {
			if ($eiField instanceof SortableEiField) {
				if (null !== ($sortField = $eiField->buildManagedSortField($eiState))) {
					ArgUtils::valTypeReturn($sortField, SortField::class, $eiField, 'buildManagedSortField', true);
					$sortDefinition->putSortField($id, $sortField);
				}
			}
			
			if ($eiField instanceof SortableEiFieldFork) {
				if (null !== ($sortFieldFork = $eiField->buildManagedSortFieldFork($eiState))) {
					ArgUtils::valTypeReturn($sortFieldFork, SortFieldFork::class, $eiField, 'buildManagedSortFieldFork', true);
					$sortDefinition->putSortFieldFork($id, $sortFieldFork);
				}
			}
		}
		
		return $sortDefinition;
	}
	
	public function createSortDefinition(N2nContext $n2nContext): SortDefinition {
		$sortDefinition = new SortDefinition();
		
		foreach ($this->eiFieldCollection as $id => $eiField) {
			if (!($eiField instanceof SortableEiField)) continue;
			
			$sortDefinition->putSortField(EiFieldPath::from($eiField), $eiField->buildSortField($n2nContext));
		}
		
		return $sortDefinition;
	}
	
	public function createQuickSearchDefinition(EiState $eiState) {
		$quickSearchDefinition = new QuickSearchDefinition();
	
		foreach ($this->eiFieldCollection as $id => $eiField) {
			if (!($eiField instanceof QuickSearchableEiField)) continue;
				
			if (null !== ($quickSearchField = $eiField->buildQuickSearchField($eiState))) {
				$quickSearchDefinition->putQuickSearchField(EiFieldPath::from($eiField), $quickSearchField);
			}
		}
	
		return $quickSearchDefinition;
	}
	
// 	public static function createSortModelFromEiState(EiState $eiState) {
// 		return self::createSortModelInstance($eiState->getContextEiMask()->getEiEngine()->getEiSpec(), $eiState->getN2nContext());
// 	}
	
	public static function createSortModel() {
		$sortModel = new SortModel();
		foreach ($this->eiFieldCollection as $id => $eiField) {
			if (!($eiField instanceof SortableEiField)) continue;
			
			if (null !== ($sortItem = $eiField->getSortItem())) {
				$sortModel->putSortItem($id, $eiField->getSortItem());
			}
			
			if (null !== ($sortItemFork = $eiField->getSortItemFork())) {
				$sortModel->putSortItemFork($id, $eiField->getSortItemFork());
			}
		}
		return $sortModel;
	}
		
// 	public static function createQuickSearchableModel(EiState $eiState) {
// 		$quickSerachModel = new QuickSearchModel();
// 		foreach ($eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getEiFieldCollection() as $field) {
// 			if ($field instanceof QuickSearchableEiField) {
// 				$quickSerachModel->addQuickSearchable($field);
// 			}
// 		}
// 		return $quickSerachModel;
// 	}
}
