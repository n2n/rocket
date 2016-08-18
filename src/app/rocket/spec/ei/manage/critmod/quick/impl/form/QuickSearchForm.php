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
namespace rocket\spec\ei\manage\critmod\quick\impl\form;

use n2n\web\dispatch\Dispatchable;
use rocket\spec\ei\manage\critmod\quick\QuickSearchDefinition;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\critmod\impl\model\CritmodSaveDao;

class QuickSearchForm implements Dispatchable {
	private $quickSearchDefinition;
	
	protected $searchStr;
	
	public function __construct(QuickSearchDefinition $quickSearchDefinition) {
		$this->querySearchDefinition = $quickSearchDefinition;
	}
	
	public function getSearchStr() {
		return $this->searchStr;
	}
	
	public function setSearchStr(string $searchStr = null) {
		$this->searchStr = $searchStr;
	}
	
	public function isActive(): bool {
		return $this->searchStr !== null;
	}
	
	private function _validation() {
		
	}
	
	public function search() {}
	
	public function clear() {
		$this->searchStr = null;
	}

	public function applyToEiState(EiState $eiState) {
	
	}
	
	
	public static function create(EiState $eiState, CritmodSaveDao $critmodSaveDao, string $stateKey = null) {
		$eiMask = $eiState->getContextEiMask();
		
		if ($stateKey === null) {
			$stateKey = uniqid();
		}
		
		return new QuickSearchForm(new QuickSearchDefinition(), $critmodSaveDao, $stateKey,
				CritmodSaveDao::buildCategoryKey($stateKey, $eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId(), $eiMask));
	}
}
