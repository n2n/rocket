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
namespace rocket\spec\ei\manage\critmod\impl\model;

use rocket\spec\ei\manage\critmod\Filter;
use n2n\context\RequestScoped;
use n2n\persistence\orm\EntityManager;
use n2n\reflection\annotation\AnnoInit;
use n2n\context\annotation\AnnoSessionScoped;
use rocket\user\model\LoginContext;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use rocket\spec\ei\manage\critmod\sort\SortData;
use n2n\util\ex\IllegalStateException;

class CritmodSaveDao implements RequestScoped {
	private static function _annos(AnnoInit $ai) {
		$ai->p('tmpCritmodSaves', new AnnoSessionScoped());
		$ai->p('selectedCritmodSaveIds', new AnnoSessionScoped());
		$ai->p('quickSearchStrings', new AnnoSessionScoped());
	}
	
	private $em;
	private $loginContext;
	private $tmpCritmodSaves = array();
	private $selectedCritmodSaveIds = array();
	private $quickSearchStrings = array();
	
	private function _init(EntityManager $em, LoginContext $loginContext) {
		$this->em = $em;
		$this->loginContext = $loginContext;
	}
	
	public static function buildCategoryKey(string $stateKey, string $eiSpecId, string $eiMaskId = null): string {
		return $stateKey . '?' . $eiSpecId . '?' . $eiMaskId;
	}

	public function setQuickSearchString(string $categoryKey, string $quickSearchString = null) {
		$this->quickSearchStrings[$categoryKey] = $quickSearchString;
	}
	
	public function getQuickSearchString(string $categoryKey) {
		if (isset($this->quickSearchStrings[$categoryKey])) {
			return $this->quickSearchStrings[$categoryKey];
		}
	
		return null;
	}
	
	public function setTmpCritmodSave(string $categoryKey, CritmodSave $critmodSave = null) {
		$this->tmpCritmodSaves[$categoryKey] = $critmodSave;
		$this->selectedCritmodSaveIds[$categoryKey] = null;
	}
	
	/**
	 * @param string $categoryKey
	 * @return CritmodSave
	 */
	public function getTmpCritmodSave(string $categoryKey) {
		if (isset($this->tmpCritmodSaves[$categoryKey])) {
			return $this->tmpCritmodSaves[$categoryKey];
		}
		
		return null;
	}
	
	public function setSelectedCritmodSave(string $categoryKey, CritmodSave $critmodSave = null) {
		$this->tmpCritmodSaves[$categoryKey] = null;
		$this->selectedCritmodSaveIds[$categoryKey] = ($critmodSave !== null ? $critmodSave->getId() : null);
	}
	
	/**
	 * @param string $categoryKey
	 * @return CritmodSave
	 */
	public function getSelectedCritmodSave(string $categoryKey) {
		if (!isset($this->selectedCritmodSaveIds[$categoryKey])) {
			return null;
		}
		
		return $this->em->find(CritmodSave::getClass(), $this->selectedCritmodSaveIds[$categoryKey]);
	}
	
	public function buildUniqueCritmodSaveName(string $eiSpecId, string $eiMaskId = null, string $filterName) {
		$realFilterName = $filterName;
		
		for ($i = 2; $this->containsCritmodSaveName($eiSpecId, $eiMaskId, $realFilterName); $i++) {
			$realFilterName = $filterName . ' ' . $i;
		}
		
		return $realFilterName;
	}
	
	public function containsCritmodSaveName($eiSpecId, string $eiMaskId = null, string $filterName) {
		$criteria = $this->em->createCriteria();
		$criteria->select('COUNT(cs)')->from(CritmodSave::getClass(), 'cs')->where(
				array('cs.eiSpecId' => $eiSpecId, 'cs.eiMaskId' => $eiMaskId));
		return (bool) $criteria->toQuery()->fetchSingle();
	}
	
	public function getCritmodSaves(string $eiSpecId, string $eiMaskId = null): array {
		return $this->em->createSimpleCriteria(CritmodSave::getClass(), array('eiSpecId' => $eiSpecId, 
						'eiMaskId' => $eiMaskId), array('name' => 'ASC'))
				->toQuery()->fetchArray();
	}
	
	public function getCritmodSaveById(string $eiSpecId, string $eiMaskId = null, int $id) {
		return $this->em->createSimpleCriteria(CritmodSave::getClass(), array('eiSpecId' => $eiSpecId,
				'eiMaskId' => $eiMaskId, 'id' => $id))->toQuery()->fetchSingle();
	}
		
// 	public function getFilterNames(EiFrame $eiFrame) {
// 		$scriptId = $eiFrame->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
// 		if (isset($this->filterDatas[$scriptId])) {
// 			return array_keys($this->filterDatas[$scriptId]);
// 		}	
		
// 		return array();
// 	}


	public function isModAccessable(): bool {
		return $this->loginContext->getCurrentUser()->isAdmin();
	}

	public function createCritmodSave(string $eiSpecId, string $eiMaskId = null, string $name, 
			FilterGroupData $filterGroupData, SortData $sortData) {
		if (!$this->isModAccessable()) {
			throw new IllegalStateException();
		}
				
		$critmodSave = new CritmodSave();
		$critmodSave->setEiSpecId($eiSpecId);
		$critmodSave->setEiMaskId($eiMaskId);
		$critmodSave->setName($name);
		$critmodSave->writeFilterData($filterGroupData);
		$critmodSave->writeSortData($sortData);
		$this->em->persist($critmodSave);
		$this->em->flush();
		return $critmodSave;
	}

	public function mergeFilter(Filter $filter) {
		return $this->em->merge($filter);
	}
	
	public function removeFilter(Filter $filter) {
		$this->em->remove($filter);
	}
	
// 	public function removeFilterDataByFilterName(EiFrame $eiFrame, $filterName) {
// 		$scriptId = $eiFrame->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
		
// 		if (isset($this->filterDatas[$scriptId])) {
// 			unset($this->filterDatas[$scriptId][$filterName]);
// 			$this->persist();
// 		}
// 	}
	
// 	private function persist() {
// 		IoUtils::putContentsSafe($this->filtersFilePath,
// 				serialize($this->filterDatas));
// 	}
}
