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
use n2n\web\dispatch\map\bind\MappingDefinition;
use rocket\ei\manage\critmod\filter\data\FilterItemData;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use n2n\web\dispatch\map\bind\BindingErrors;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\web\dispatch\map\MappingResult;
use n2n\web\dispatch\DispatchContext;
use n2n\util\ex\IllegalStateException;
use n2n\core\container\N2nContext;

class FilterFieldItemForm implements Dispatchable {
	private $filterItemData;
	private $filterModel;
	
	protected $filterFieldId;
	protected $magForm;
	
	public function __construct(FilterItemData $filterItemData, FilterDefinition $filterDefinition) {
		$this->filterItemData = $filterItemData;
		$this->filterModel = $filterDefinition;
		
		$this->filterFieldId = $filterItemData->getFilterFieldId();
		if ($this->filterFieldId !== null && null !== ($filterItem = $filterDefinition->getFilterFieldById($this->filterFieldId))) {
			$this->magForm = $filterItem->createMagDispatchable($filterItemData->getAttributes());
		}
	}
	
	private function _mapping(MappingResult $mr, MappingDefinition $md, DispatchContext $dc, BindingErrors $be, 
			N2nContext $n2nContext) {
		if (!$md->isDispatched()) return;
		
		$filterField = null;
		if (null !== ($fieldFieldId = $md->getDispatchedValue('filterFieldId'))) {
			$filterField = $this->filterModel->getFilterFieldById($fieldFieldId);
		}
		
		if ($filterField === null) {
			$be->addError('filterFieldId', 'Invalid filter item.');
			return;
		}
		
		$this->magForm = $filterField->createMagDispatchable($this->filterItemData->getAttributes());
// 		$mr->magForm = $dc->getOrCreateMappingResult($magForm, $n2nContext);
	}
	
	private function _validation() {
	}
	
	public function setFilterFieldId(string $filterFieldId) {
		$this->filterFieldId = $filterFieldId;
	}
	
	public function getFilterFieldId() {
		return $this->filterFieldId;
	}
	
	public function setMagForm(MagDispatchable $magForm) {
		$this->magForm = $magForm;
	}
	
	public function getMagForm() {
		return $this->magForm;
	}
	
	public function buildFilterItemData(): FilterItemData {
		$filterItem = $this->filterModel->getFilterFieldById($this->filterFieldId);
		if ($filterItem === null) {
			throw new IllegalStateException();
		}
		
		$this->filterItemData->setFilterFieldId($this->filterFieldId);
		$this->filterItemData->setAttributes($filterItem->buildAttributes($this->magForm));
	
		return $this->filterItemData;
	}
}
