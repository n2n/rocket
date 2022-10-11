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
namespace rocket\ei\component;

 
use rocket\ei\component\prop\EiPropCollection;
use rocket\ei\component\modificator\EiModCollection;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\critmod\filter\FilterDefinition;

use n2n\core\container\N2nContext;
use rocket\ei\EiPropPath;
use rocket\ei\manage\critmod\sort\SortDefinition;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\critmod\sort\SortProp;

use rocket\ei\manage\critmod\sort\SortPropFork;
use rocket\ei\manage\critmod\filter\FilterProp;

use rocket\ei\manage\critmod\quick\QuickSearchDefinition;
use rocket\ei\util\Eiu;

class CritmodFactory {
	private $eiPropCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiPropCollection $eiPropCollection, EiModCollection $eiModificatorCollection) {
		$this->eiPropCollection = $eiPropCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}
	
// 	public static function createFilterModel(EiType $eiType, N2nContext $n2nContext) {
// 		return self::createFilterModelInstance($eiType, $n2nContext);
// 	}
	
// 	public static function createFilterModelFromEiFrame(EiFrame $eiFrame) {
// 		return self::createFilterModelInstance($eiFrame->getContextEiEngine()->getEiMask()->getEiType(), 
// 				$eiFrame->getN2nContext(), $eiFrame);
// 	}
	
	public function createFramedFilterDefinition(EiFrame $eiFrame): FilterDefinition {
		$eiu = new Eiu($eiFrame);
		
		$filterDefinition = new FilterDefinition();
		foreach ($this->eiPropCollection as $id => $eiProp) {
			$filterProp = $eiProp->buildFilterProp($eiu);
			ArgUtils::valTypeReturn($filterProp, FilterProp::class, $eiProp, 'buildManagedFilterProp', true);
			
			if ($filterProp !== null) {
				$filterDefinition->putFilterProp($id, $filterProp);
			}
		}		
		return $filterDefinition;
	}
	
	public function createFilterDefinition(N2nContext $n2nContext): FilterDefinition {
		$eiu = new Eiu($n2nContext);
		
		$filterDefinition = new FilterDefinition();
		foreach ($this->eiPropCollection as $id => $eiProp) {
			$filterProp = $eiProp->getNature()->buildFilterProp($eiu);

			if ($filterProp !== null) {
				$filterDefinition->putFilterProp($id, $filterProp);
			}
		}
		return $filterDefinition;
	}
	
	public function createFramedSortDefinition(EiFrame $eiFrame): SortDefinition {
		$eiu = new Eiu($eiFrame);
		$sortDefinition = new SortDefinition();
		
		foreach ($this->eiPropCollection as $eiPropPathStr => $eiProp) {
			f (null !== ($sortProp = $eiProp->getNature()->buildSortProp($eiu))) {
				$sortDefinition->putSortProp(EiPropPath::create($eiPropPathStr), $sortProp);
			}

			if (null !== ($sortPropFork = $eiProp->buildSortPropFork($eiu))) {
				ArgUtils::valTypeReturn($sortPropFork, SortPropFork::class, $eiProp, 'buildSortPropFork', true);
				$sortDefinition->putSortPropFork(EiPropPath::create($eiPropPathStr), $sortPropFork);
			}
		}
		
		return $sortDefinition;
	}
	
	public function createSortDefinition(N2nContext $n2nContext): SortDefinition {
		$eiu = new Eiu($n2nContext);
		$sortDefinition = new SortDefinition();
		
		foreach ($this->eiPropCollection as $eiPropPathStr => $eiProp) {
			if (null !== ($sortProp = $eiProp->getNature()->buildSortProp($eiu))) {
				$sortDefinition->putSortProp($eiProp->getEiPropPath(), $sortProp);
			}
			
			if (null !== ($sortPropFork = $eiProp->getNature()->buildSortPropFork($eiu))) {
				$sortDefinition->putSortPropFork($eiProp->getEiPropPath(), $sortPropFork);
			}
		}
		
		return $sortDefinition;
	}
	
	public function createFramedQuickSearchDefinition(EiFrame $eiFrame) {
		$eiu = new Eiu($eiFrame);
		$quickSearchDefinition = new QuickSearchDefinition($this->eiPropCollection->getEiMask());
	
		foreach ($this->eiPropCollection as $id => $eiProp) {
			if (null !== ($quickSearchField = $eiProp->buildQuickSearchProp($eiu))) {
				$quickSearchDefinition->putQuickSearchProp(EiPropPath::from($eiProp), $quickSearchField);
			}
		}
	
		return $quickSearchDefinition;
	}

}
