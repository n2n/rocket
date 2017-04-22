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
namespace rocket\spec\ei\manage\critmod\filter;

use rocket\spec\ei\EiPropPath;
use n2n\util\ex\UnsupportedOperationException;
use rocket\spec\ei\manage\mapping\EiMappingConstraint;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\EiFieldConstraint;
use n2n\util\config\AttributesException;

class EiMappingFilterDefinition extends FilterDefinition {
	private $eiMappingFilterFields = array();
	
	public function putFilterField(string $id, FilterField $filterField) {
		throw new UnsupportedOperationException();
	}
	
	public function putEiMappingFilterField(EiPropPath $eiPropPath, EiMappingFilterField $eiMappingFilterField) {
		$this->eiMappingFilterFields[(string) $eiPropPath] = $eiMappingFilterField;
		parent::putFilterField($eiPropPath, $eiMappingFilterField);
	}
	
	public function getEiMappingFilterFields(): array {
		return $this->eiMappingFilterFields;
	}
	
	public function createEiMappingConstraint(FilterGroupData $filterGroupData): EiMappingConstraint  {
		$eiMappingConstraints = array();
	
		foreach ($filterGroupData->getFilterItemDatas() as $subFilterItemData) {
			$id = $subFilterItemData->getFilterFieldId();
			if (!isset($this->eiMappingFilterFields[$id])) {
				continue;
			}
				
			try {
				$eiMappingConstraints[] = new EiFieldEiMappingConstraint(EiPropPath::create($id),
						$this->eiMappingFilterFields[$id]->createEiFieldConstraint(
								$subFilterItemData->getAttributes()));
			} catch (AttributesException $e) {}
		}
	
		foreach ($filterGroupData->getFilterGroupDatas() as $subFilterGroupData) {
			$eiMappingConstraints[] = $this->createEiMappingConstraint($subFilterGroupData);
		}
	
		return new EiMappingConstraintGroup($filterGroupData->isAndUsed(), $eiMappingConstraints);
	}
}

class EiMappingConstraintGroup implements EiMappingConstraint {
	private $andUsed;
	private $eiMappingConstraints;
	
	public function __construct(bool $andUsed, array $eiMappingConstraints = array()) {
		$this->andUsed = $andUsed;
		ArgUtils::valArray($eiMappingConstraints, EiMappingConstraint::class);
		$this->eiMappingConstraints = $eiMappingConstraints;
	}
	
	public function add(EiMappingConstraint $eiMappingConstraint) {
		$this->eiMappingConstraints[] = $eiMappingConstraint;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::acceptsValue($eiPropPath, $value)
	 */
	public function acceptsValue(EiPropPath $eiPropPath, $value): bool {
		foreach ($this->eiMappingConstraints as $eiMappingConstraint) {
			if ($eiMappingConstraint->acceptsValue($eiPropPath)) {
				if (!$this->andUsed) return true;
			} else {
				if ($this->andUsed) return false;
			}
		}
		
		return $this->andUsed;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::check($eiMapping)
	 */
	public function check(EiMapping $eiMapping): bool {
		foreach ($this->eiMappingConstraints as $eiMappingConstraint) {
			if ($eiMappingConstraint->check($eiMapping)) {
				if (!$this->andUsed) return true;
			} else {
				if ($this->andUsed) return false;
			}
		}
		
		return $this->andUsed;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::validate($eiMapping)
	 */
	public function validate(EiMapping $eiMapping) {
		if (!$this->andUsed) {
			foreach ($this->eiMappingConstraints as $eiMappingConstraint) {
				if ($eiMappingConstraint->check($eiMapping)) return;
			}
		}
		
		foreach ($this->eiMappingConstraints as $eiMappingConstraint) {
			$eiMappingConstraint->validate($eiMapping);
		}
	}
}

class EiFieldEiMappingConstraint implements EiMappingConstraint {
	private $eiPropPath;
	private $eiFieldConstraint;
	
	public function __construct(EiPropPath $eiPropPath, EiFieldConstraint $eiFieldConstraint) {
		$this->eiPropPath = $eiPropPath;
		$this->eiFieldConstraint = $eiFieldConstraint;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::acceptsValue($eiPropPath, $value)
	 */
	public function acceptsValue(EiPropPath $eiPropPath, $value): bool {
		if (!$this->eiPropPath->equals($eiPropPath)) return;
		
		return $this->eiFieldConstraint->acceptsValue($value);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::check($eiMapping)
	 */
	public function check(EiMapping $eiMapping): bool {
		return $this->eiFieldConstraint->check($eiMapping
				->getEiField($this->eiPropPath));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::validate($eiMapping)
	 */
	public function validate(EiMapping $eiMapping) {
		return $this->eiFieldConstraint->validate($eiMapping->getEiField($this->eiPropPath), 
				$eiMapping->getMappingErrorInfo()->getFieldErrorInfo($this->eiPropPath));
	}
}
