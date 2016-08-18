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

use rocket\spec\ei\EiFieldPath;
use n2n\util\ex\UnsupportedOperationException;
use rocket\spec\ei\manage\mapping\EiMappingConstraint;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\MappableConstraint;
use n2n\util\config\AttributesException;

class EiMappingFilterDefinition extends FilterDefinition {
	private $eiMappingFilterFields = array();
	
	public function putFilterField(string $id, FilterField $filterField) {
		throw new UnsupportedOperationException();
	}
	
	public function putEiMappingFilterField(EiFieldPath $eiFieldPath, EiMappingFilterField $eiMappingFilterField) {
		$this->eiMappingFilterFields[(string) $eiFieldPath] = $eiMappingFilterField;
		parent::putFilterField($eiFieldPath, $eiMappingFilterField);
	}
	
	public function getEiMappingFilterFields(): array {
		return $this->eiMappingFilterFields;
	}
	
	public function createEimappingConstraint(FilterGroupData $filterGroupData): EiMappingConstraint  {
		$eiMappingConstraints = array();
	
		foreach ($filterGroupData->getFilterItemDatas() as $subFilterItemData) {
			$id = $subFilterItemData->getFilterFieldId();
			if (!isset($this->eiMappingFilterFields[$id])) {
				continue;
			}
				
			try {
				$eiMappingConstraints[] = new MappableEiMappingConstraint(EiFieldPath::create($id),
						$this->eiMappingFilterFields[$id]->createMappableConstraint(
								$subFilterItemData->getAttributes()));
			} catch (AttributesException $e) {}
		}
	
		foreach ($filterGroupData->getFilterGroupDatas() as $subFilterGroupData) {
			$eiMappingConstraints[] = $this->createEimappingConstraint($subFilterGroupData);
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
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::acceptsValue($eiFieldPath, $value)
	 */
	public function acceptsValue(EiFieldPath $eiFieldPath, $value): bool {
		foreach ($this->eiMappingConstraints as $eiMappingConstraint) {
			if ($eiMappingConstraint->acceptsValue($eiFieldPath)) {
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

class MappableEiMappingConstraint implements EiMappingConstraint {
	private $eiFieldPath;
	private $mappableConstraint;
	
	public function __construct(EiFieldPath $eiFieldPath, MappableConstraint $mappableConstraint) {
		$this->eiFieldPath = $eiFieldPath;
		$this->mappableConstraint = $mappableConstraint;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::acceptsValue($eiFieldPath, $value)
	 */
	public function acceptsValue(EiFieldPath $eiFieldPath, $value): bool {
		if (!$this->eiFieldPath->equals($eiFieldPath)) return;
		
		return $this->mappableConstraint->acceptsValue($value);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::check($eiMapping)
	 */
	public function check(EiMapping $eiMapping): bool {
		return $this->mappableConstraint->check($eiMapping->getMappingProfile()
				->getMappable($this->eiFieldPath));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\EiMappingConstraint::validate($eiMapping)
	 */
	public function validate(EiMapping $eiMapping) {
		return $this->mappableConstraint->validate($eiMapping->getMappingProfile()->getMappable($this->eiFieldPath), 
				$eiMapping->getMappingErrorInfo()->getFieldErrorInfo($this->eiFieldPath));
	}
}
