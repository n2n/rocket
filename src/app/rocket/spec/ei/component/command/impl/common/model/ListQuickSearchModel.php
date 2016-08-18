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
namespace rocket\spec\ei\component\command\impl\common\model;

use n2n\web\dispatch\Dispatchable;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\CritmodFactory;

class ListQuickSearchModel implements Dispatchable {	
	private $eiState;
	private $tmpFilterStore;
	private $quickSearchableModel;
	
	protected $searchStr;
	
	public function __construct(EiState $eiState, ListTmpFilterStore $tmpFilterStore) {
		$this->eiState = $eiState;
		$this->tmpFilterStore = $tmpFilterStore;
		$this->quickSearchableModel = CritmodFactory::createQuickSearchableModel($eiState);
		$this->searchStr = $tmpFilterStore->getTmpSearchStr($eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId());
	}
	
	public function isActive() {
		return $this->searchStr !== null;
	}
	
	public function applyToEiState(EiState $eiState) {
		$criteriaConstraint = $this->quickSearchableModel->createCriteriaConstraint($this->searchStr);
		if ($criteriaConstraint !== null) {
			$eiState->addCriteriaConstraint($criteriaConstraint);
		}
	}
	
	public function getSearchStr() {
		return $this->searchStr;
	}
	
	public function setSearchStr($searchStr) {
		$this->searchStr = $searchStr;
	}
	
	private function _validation() { }
	
	public function search() {
		$this->tmpFilterStore->setTmpSearchStr($this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId(), $this->searchStr);
	}
	
	public function clear() {
		$this->tmpFilterStore->setTmpSearchStr($this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId(), null);
	}
	
}
