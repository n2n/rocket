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
// namespace rocket\ei\security;

// use rocket\ei\manage\security\CommandExecutionConstraint;
// use rocket\ei\manage\mapping\MappingValidationResult;
// use n2n\persistence\orm\criteria\compare\CriteriaComparator;
// use n2n\persistence\orm\criteria\Criteria;
// use rocket\ei\manage\critmod\SelectorValidationResult;
// use n2n\l10n\MessageCode;
// use rocket\ei\manage\security\MappingArrayAccess;
// use rocket\ei\manage\mapping\EiEntry;
// use n2n\persistence\orm\criteria\item\CriteriaConstant;
// use n2n\persistence\orm\criteria\item\CriteriaProperty;

// class EiCommandExecutionConstraint implements CommandExecutionConstraint {
// 	private $privilegesGrantItems = array();
	
// 	public function __construct(array $privilegesGrantItems) {
// 		$this->privilegesGrantItems = $privilegesGrantItems;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \rocket\ei\manage\mapping\EiEntryConstraint::acceptValues()
// 	 */
// 	public function acceptValues(\ArrayAccess $values) {
// 		foreach ($this->privilegesGrantItems as $item) {
// 			if ($item->acceptValues($values)) return true;
// 		}
		
// 		return false;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \rocket\ei\manage\mapping\EiEntryConstraint::acceptValue()
// 	 */
// 	public function acceptValue($id, $value) {
// 		foreach ($this->privilegesGrantItems as $item) {
// 			if ($item->acceptValue($id, $value)) return true;
// 		}
		
// 		return false;
// 	}

// 	/* (non-PHPdoc)
// 	 * @see \rocket\ei\manage\mapping\MappingValidator::validate()
// 	 */
// 	public function validate(EiEntry $eiEntry) {
// 		$values = new MappingArrayAccess($eiEntry, false);
// 		$validationResults = array();
// 		foreach ($this->privilegesGrantItems as $accessGrants) {
// 			$validationResult = new SelectorValidationResult();
// 			if ($accessGrants->validateValues($values, $validationResult)) {
// 				return true;
// 			}
// 			$validationResults[] = $validationResult;
// 		}
		
// 		$mappingValidationResult->addError(null, new MessageCode('no_access_to_values'));
		
// 		return false;
// 	}

// 	/* (non-PHPdoc)
// 	 * @see \rocket\ei\manage\critmod\CriteriaConstraint::applyToCriteria()
// 	 */
// 	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias) {
// 		$comparatorConstraints = array();
// 		foreach ($this->privilegesGrantItems as $privilegesGrantItem) {
// 			$comparatorConstraint = $privilegesGrantItem->getComparatorConstraint();
// 			if ($comparatorConstraint === null) return;
// 			$comparatorConstraints[] = $comparatorConstraint;
// 		}
		
// 		if (0 == count($comparatorConstraints)) {
// 			$group = $criteria->where()->andMatch(new CriteriaConstant(1), '=', new CriteriaConstant(2));
// 		}
		
// 		$group = $criteria->where()->andGroup();
// 		foreach ($comparatorConstraints as $comparatorConstraint) {
// 			$comparatorConstraint->applyToCriteriaComparator($group, $alias, false);
// 		}	
// 	}

// }
