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
namespace rocket\impl\ei\component\command\common\model\critmod;

use rocket\ei\manage\frame\EiFrame;
use n2n\web\dispatch\Dispatchable;
use n2n\web\dispatch\map\bind\BindingDefinition;
use rocket\ei\manage\frame\CriteriaConstraint;
use rocket\ei\manage\critmod\filter\ComparatorConstraintGroup;
use n2n\web\dispatch\map\bind\MappingDefinition;
use n2n\l10n\DynamicTextCollection;
use rocket\ei\manage\critmod\save\CritmodSaveDao;
use rocket\spec\TypePath;
use rocket\ei\manage\critmod\save\CritmodSave;
use rocket\ei\util\model\EiuFrame;
use rocket\ei\util\filter\controller\FilterJhtmlHook;
use rocket\ei\util\filter\EiuFilterForm;
use rocket\ei\util\sort\EiuSortForm;

class CritmodForm implements Dispatchable {	
	private $critmodSaveDao;
// 	private $stateKey;
	private $categoryKey;
	private $eiTypePath;
	private $active = false;
	
	protected $name;
	protected $selectedCritmodSaveId;
	protected $eiuFilterForm;
	protected $eiuSortForm;
	
	public function __construct(EiuFilterForm $eiuFilterForm, EiuSortForm $eiuSortForm, 
			CritmodSaveDao $critmodSaveDao, string $stateKey, TypePath $eiTypePath) {
		$this->eiuFilterForm = $eiuFilterForm;
		$this->eiuSortForm = $eiuSortForm;
				
		$this->critmodSaveDao = $critmodSaveDao;
		$this->categoryKey = CritmodSaveDao::buildCategoryKey($stateKey, $eiTypePath);
		$this->eiTypePath = $eiTypePath;
				
		if (null !== ($tmpCritmodSave = $this->critmodSaveDao->getTmpCritmodSave($this->categoryKey))) {
			$this->name = $tmpCritmodSave->getName();
			$this->eiuFilterForm->setSettings($tmpCritmodSave->readFilterPropSettingGroup());
			$this->eiuSortForm->writeSetting($tmpCritmodSave->readSortSetting());
			$this->active = true;
			if (null !== $selectedCritmodSave = $critmodSaveDao->getSelectedCritmodSave($this->categoryKey)) {
				$this->selectedCritmodSaveId = $selectedCritmodSave->getId();
			}	
		} else if (null !== ($selectedCritmodSave = $critmodSaveDao->getSelectedCritmodSave($this->categoryKey))) {
			$this->selectedCritmodSaveId = $selectedCritmodSave->getId();
			$this->name = $selectedCritmodSave->getName();
			$this->eiuFilterForm->setSettings($selectedCritmodSave->readFilterPropSettingGroup());
			$this->eiuSortForm->writeSetting($selectedCritmodSave->readSortSetting());
			$this->active = true;
		} else {
			$this->active = false;
		}
	}
	
	public function isActive(): bool {
		return $this->active;
	}
	
// 	public function getStateKey(): string {
// 		return $this->stateKey;
// 	}
		
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string $selectedCritmodSaveId
	 */
	public function getSelectedCritmodSaveId() {
		return $this->selectedCritmodSaveId;
	}

	/**
	 * @param string $selectedCritmodSaveId
	 */
	public function setSelectedCritmodSaveId($selectedCritmodSaveId) {
		$this->selectedCritmodSaveId = $selectedCritmodSaveId;
	}

	public function getSelectedCritmodSaveIdOptions(): array {
		$options = array(null => '');
		foreach ($this->critmodSaveDao->getCritmodSaves($this->eiTypePath) as $critmodSave) {
			$options[$critmodSave->getId()] = $critmodSave->getName();
		}
		return $options;
	}
	
	/**
	 * @return EiuFilterForm
	 */
	public function getEiuFilterForm(): EiuFilterForm {
		return $this->eiuFilterForm;
	}

	/**
	 * @param EiuFilterForm $eiuFilterForm
	 */
	public function setEiuFilterForm(EiuFilterForm $eiuFilterForm) {
		$this->eiuFilterForm = $eiuFilterForm;
	}

	
	/**
	 * @return EiuSortForm
	 */
	public function getEiuSortForm(): EiuSortForm {
		return $this->eiuSortForm;
	}

	/**
	 * @param EiuSortForm $eiuSortForm
	 */
	public function setEiuSortForm(EiuSortForm $eiuSortForm) {
		$this->eiuSortForm = $eiuSortForm;
	}
	
	private function _mapping(MappingDefinition $md) {
		if ($md->getMethodName() == 'delete' || $md->getMethodName() == 'clear' || $md->getMethodName() == 'select') {
			$md->ignore('filterGroupForm');
			$md->ignore('sortForm');
		}
	}
	
	private function _validation(BindingDefinition $bd) {
	}

	public function applyToEiFrame(EiFrame $eiFrame, bool $tmp) {
		$critmodSave = $this->critmodSaveDao->getTmpCritmodSave($this->categoryKey);
		if ($critmodSave === null) {
			$critmodSave = $this->critmodSaveDao->getSelectedCritmodSave($this->categoryKey);
			if ($critmodSave === null) return;
		}

		$comparatorConstraint = $this->getFilterGroupForm()->getFilterDefinition()
						->createComparatorConstraint($critmodSave->readFilterPropSettingGroup());
		$eiFrame->getCriteriaConstraintCollection()->add(
				($tmp ? CriteriaConstraint::TYPE_TMP_FILTER : CriteriaConstraint::TYPE_HARD_FILTER),
				new ComparatorConstraintGroup(true, array($comparatorConstraint)));
		
		$sortCriteriaConstraint = $this->getEiuSortForm()->getSortDefinition()
				->builCriteriaConstraint($critmodSave->readSortSetting(), $tmp);
		if ($sortCriteriaConstraint !== null) {
			$eiFrame->getCriteriaConstraintCollection()->add(
					($tmp ? CriteriaConstraint::TYPE_TMP_SORT : CriteriaConstraint::TYPE_HARD_SORT),
					$sortCriteriaConstraint);
		}
	}
	
// 	public function getSelectOptions(): array {
		
// 	}
	
	public function select() {
		$critmodSave = null;
		if ($this->selectedCritmodSaveId !== null) {
			$critmodSave = $this->critmodSaveDao->getCritmodSaveById(
					$this->eiTypeId, $this->eiMaskId, $this->selectedCritmodSaveId);
		}
		
		$this->critmodSaveDao->setTmpCritmodSave($this->categoryKey, null);
		$this->critmodSaveDao->setSelectedCritmodSave($this->categoryKey, $critmodSave);
	}
	
	public function apply() {
		$critmodSave = new CritmodSave();
		$critmodSave->setEiTypeId($this->eiTypeId);
		$critmodSave->setEiMaskId($this->eiMaskId);
		$critmodSave->writeFilterData($this->eiuFilterForm->buildFilterPropSettingGroup());
		$critmodSave->writeSortSetting($this->eiuSortForm->buildSortSetting());
		$this->critmodSaveDao->setTmpCritmodSave($this->categoryKey, $critmodSave);
		
		$this->active = true;
	}
	
	public function save(DynamicTextCollection $dtc) {
		$critmodSave = $this->critmodSaveDao->getSelectedCritmodSave($this->categoryKey);
		if ($critmodSave === null) {
			$this->saveAs($dtc);
			return;
		}
		
		if ($this->name === null) {
			$this->name = $dtc->t('common_untitled_label');
		}
		
		$this->name = $this->critmodSaveDao->buildUniqueCritmodSaveName($this->eiTypeId, $this->eiMaskId,
				$this->name, $critmodSave);
		
		$critmodSave->setName($this->name);
		$critmodSave->writeFilterData($this->eiuFilterForm->buildFilterPropSettingGroup());
		$critmodSave->writeSortSetting($this->eiuSortForm->buildSortSetting());
		
		$this->name = $critmodSave->getName();
	}
	
	public function saveAs(DynamicTextCollection $dtc) {
		if ($this->name === null) {
			$this->name = $dtc->t('common_untitled_label');
		}
		
		$this->name = $this->critmodSaveDao->buildUniqueCritmodSaveName($this->eiTypeId, $this->eiMaskId, $this->name);
		
		$critmodSave = $this->critmodSaveDao->createCritmodSave($this->eiTypeId, $this->eiMaskId, $this->name, 
				$this->eiuFilterForm->buildFilterPropSettingGroup(), 
				$this->eiuSortForm->buildSortSetting());
		
		$this->critmodSaveDao->setSelectedCritmodSave($this->categoryKey, $critmodSave);
		$this->active = true;
	}
	
	public function clear() {
		$this->critmodSaveDao->setSelectedCritmodSave($this->categoryKey, null);
		$this->critmodSaveDao->setTmpCritmodSave($this->categoryKey, null);
		
		$this->eiuFilterForm->clear();
		$this->eiuSortForm->clear();
		
		$this->active = false;
	}
	
	public function delete() {
		$critmodSave = $this->critmodSaveDao->getSelectedCritmodSave($this->categoryKey);
		if ($critmodSave === null) return;
		
		$this->critmodSaveDao->removeCritmodSave($critmodSave);
		$this->clear();
	}
	
	public static function create(EiuFrame $eiuFrame, FilterJhtmlHook $filterJhtmlHook, 
			CritmodSaveDao $critmodSaveDao, string $stateKey): CritmodForm {
		
		return new CritmodForm(
				$eiuFrame->newFilterForm($filterJhtmlHook),
				$eiuFrame->newSortForm(),
				$critmodSaveDao, $stateKey, $eiuFrame->getContextEiTypePath());
	}
}
