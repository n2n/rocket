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
namespace rocket\ei\manage\critmod\filter\impl\form;

use n2n\web\dispatch\Dispatchable;
use rocket\ei\manage\critmod\filter\data\FilterPropSetting;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\manage\critmod\filter\data\FilterPropSettingGroup;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObjectArray;
use n2n\util\config\Attributes;
use n2n\web\dispatch\map\bind\BindingDefinition;

class FilterGroupForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('filterPropItemForms', new AnnoDispObjectArray(function (FilterGroupForm $filterGroupForm) {
			return new FilterPropItemForm(new FilterPropSetting(null, new Attributes()), $filterGroupForm->filterDefinition);
		}));
		$ai->p('filterGroupForms', new AnnoDispObjectArray(function (FilterGroupForm $filterGroupForm) {
			return new FilterGroupForm(new FilterPropSettingGroup(), $filterGroupForm->filterDefinition);
		}));
	}
	
	private $filterPropSettingGroup;
	private $filterDefinition;
	
	protected $useAnd;
	protected $filterPropItemForms;
	protected $filterGroupForms;
	
	public function __construct(FilterPropSettingGroup $filterPropSettingGroup, FilterDefinition $filterDefinition) {
		$this->filterPropSettingGroup = $filterPropSettingGroup;
		$this->filterDefinition = $filterDefinition;
		
		$this->useAnd = $filterPropSettingGroup->isAndUsed();
		
		$this->filterPropItemForms = array();
		foreach ($filterPropSettingGroup->getFilterPropSettings() as $filterPropSetting) {
			$this->filterPropItemForms[] = new FilterPropItemForm($filterPropSetting, $filterDefinition);
		}
		
		$this->filterGroupForms = array();
		foreach ($filterPropSettingGroup->getFilterPropSettingGroups() as $filterPropSettingGroup) {
			$this->filterGroupForms[] = new FilterGroupForm($filterPropSettingGroup, $filterDefinition);
		}
	}
	
	public function getFilterDefinition(): FilterDefinition {
		return $this->filterDefinition;
	}

	public function setUseAnd($useAnd) {
		$this->useAnd = (bool) $useAnd;
	}
	
	public function isUseAnd(): bool {
		return $this->useAnd;
	}
	
	public function setFilterPropItemForms(array $filterPropItemForms) {
		$this->filterPropItemForms = $filterPropItemForms;
	}
	
	public function getFilterPropItemForms(): array {
		return $this->filterPropItemForms;
	}
	
	public function setFilterGroupForms(array $filterGroupForms) {
		$this->filterGroupForms = $filterGroupForms;
	}
	
	public function getFilterGroupForms(): array {
		return $this->filterGroupForms;
	}
	
	public function clear() {
		$this->filterPropItemForms = array();
		$this->filterGroupForms = array();
	}
	
// 	private function _mapping(MappingResult $mr, MappingDefinition $md, DispatchContext $dc, BindingErrors $be) {
// 		if (!$md->isDispatched()) return;
		
// 		$fieldItemId = $md->getDispatchedValue('fieldItemId');
// 		$filterItem = null;
// 		if (is_scalar($fieldItemId)) {
// 			$filterItem = $this->filterDefinition->getFilterPropById($fieldItemId);
// 		}
		
// 		if ($filterItem === null) {
// 			$be->addError('fieldItemId', 'Invalid filter item.');
// 			return;
// 		}
		
// 		$magForm = new MagDispatchable($filterItem->createMagCollection($this->filterPropSettingGroup->getAttributes()));
// 		$mr->magForm = new MappingResult($magForm, $dc->getDispatchModelManager()->getDispatchModel($magForm));
// 	}
	
	private function _validation(BindingDefinition $bd) {
		
	}
	
	public function buildFilterPropSettingGroup(): FilterPropSettingGroup {
		$this->filterPropSettingGroup->setAndUsed($this->useAnd);
		
		$filterItemDatas = $this->filterPropSettingGroup->getFilterPropSettings();
		$filterItemDatas->clear();
		foreach ($this->filterPropItemForms as $filterPropItemForm) {
			$filterItemDatas->append($filterPropItemForm->buildFilterPropSetting());
		}
		
		$filterPropSettingGroups = $this->filterPropSettingGroup->getFilterPropSettingGroups();
		$filterPropSettingGroups->clear();
		foreach ($this->filterGroupForms as $filterGroupForm) {
			$filterPropSettingGroups->append($filterGroupForm->buildFilterPropSettingGroup());
		}
		
		return $this->filterPropSettingGroup;
	}
}
