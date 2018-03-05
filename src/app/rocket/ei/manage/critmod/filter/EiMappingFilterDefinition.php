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
namespace rocket\ei\manage\critmod\filter;

use rocket\ei\EiPropPath;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ei\manage\mapping\EiEntryConstraint;
use rocket\ei\manage\critmod\filter\data\FilterGroupData;
use rocket\ei\manage\mapping\EiEntry;
use n2n\reflection\ArgUtils;
use rocket\ei\manage\mapping\EiFieldConstraint;
use n2n\util\config\AttributesException;

class EiEntryFilterDefinition extends FilterDefinition {
	private $eiEntryFilterFields = array();
	
	public function putFilterField(string $id, FilterField $filterField) {
		throw new UnsupportedOperationException();
	}
	
	public function putEiEntryFilterField(EiPropPath $eiPropPath, EiEntryFilterField $eiEntryFilterField) {
		$this->eiEntryFilterFields[(string) $eiPropPath] = $eiEntryFilterField;
		parent::putFilterField($eiPropPath, $eiEntryFilterField);
	}
	
	public function getEiEntryFilterFields(): array {
		return $this->eiEntryFilterFields;
	}
	
	public function createEiEntryConstraint(FilterGroupData $filterGroupData): EiEntryConstraint  {
		$eiEntryConstraints = array();
	
		foreach ($filterGroupData->getFilterItemDatas() as $subFilterItemData) {
			$id = $subFilterItemData->getFilterFieldId();
			if (!isset($this->eiEntryFilterFields[$id])) {
				continue;
			}
				
			try {
				$eiEntryConstraints[] = new EiFieldEiEntryConstraint(EiPropPath::create($id),
						$this->eiEntryFilterFields[$id]->createEiFieldConstraint(
								$subFilterItemData->getAttributes()));
			} catch (AttributesException $e) {}
		}
	
		foreach ($filterGroupData->getFilterGroupDatas() as $subFilterGroupData) {
			$eiEntryConstraints[] = $this->createEiEntryConstraint($subFilterGroupData);
		}
	
		return new EiEntryConstraintGroup($filterGroupData->isAndUsed(), $eiEntryConstraints);
	}
}

class EiEntryConstraintGroup implements EiEntryConstraint {
	private $andUsed;
	private $eiEntryConstraints;
	
	public function __construct(bool $andUsed, array $eiEntryConstraints = array()) {
		$this->andUsed = $andUsed;
		ArgUtils::valArray($eiEntryConstraints, EiEntryConstraint::class);
		$this->eiEntryConstraints = $eiEntryConstraints;
	}
	
	public function add(EiEntryConstraint $eiEntryConstraint) {
		$this->eiEntryConstraints[] = $eiEntryConstraint;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiEntryConstraint::acceptsValue($eiPropPath, $value)
	 */
	public function acceptsValue(EiPropPath $eiPropPath, $value): bool {
		foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
			if ($eiEntryConstraint->acceptsValue($eiPropPath)) {
				if (!$this->andUsed) return true;
			} else {
				if ($this->andUsed) return false;
			}
		}
		
		return $this->andUsed;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiEntryConstraint::check($eiEntry)
	 */
	public function check(EiEntry $eiEntry): bool {
		foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
			if ($eiEntryConstraint->check($eiEntry)) {
				if (!$this->andUsed) return true;
			} else {
				if ($this->andUsed) return false;
			}
		}
		
		return $this->andUsed;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiEntryConstraint::validate($eiEntry)
	 */
	public function validate(EiEntry $eiEntry) {
		if (!$this->andUsed) {
			foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
				if ($eiEntryConstraint->check($eiEntry)) return;
			}
		}
		
		foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
			$eiEntryConstraint->validate($eiEntry);
		}
	}
}

class EiFieldEiEntryConstraint implements EiEntryConstraint {
	private $eiPropPath;
	private $eiFieldConstraint;
	
	public function __construct(EiPropPath $eiPropPath, EiFieldConstraint $eiFieldConstraint) {
		$this->eiPropPath = $eiPropPath;
		$this->eiFieldConstraint = $eiFieldConstraint;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiEntryConstraint::acceptsValue($eiPropPath, $value)
	 */
	public function acceptsValue(EiPropPath $eiPropPath, $value): bool {
		if (!$this->eiPropPath->equals($eiPropPath)) return;
		
		return $this->eiFieldConstraint->acceptsValue($value);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiEntryConstraint::check($eiEntry)
	 */
	public function check(EiEntry $eiEntry): bool {
		return $this->eiFieldConstraint->check($eiEntry
				->getEiField($this->eiPropPath));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiEntryConstraint::validate($eiEntry)
	 */
	public function validate(EiEntry $eiEntry) {
		return $this->eiFieldConstraint->validate($eiEntry->getEiField($this->eiPropPath), 
				$eiEntry->getMappingErrorInfo()->getFieldErrorInfo($this->eiPropPath));
	}
}
