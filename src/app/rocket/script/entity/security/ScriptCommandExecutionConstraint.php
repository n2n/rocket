<?php

namespace rocket\script\entity\security;

use rocket\script\entity\manage\security\CommandExecutionConstraint;
use rocket\script\entity\manage\mapping\MappingValidationResult;
use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\criteria\Criteria;
use rocket\script\entity\filter\SelectorValidationResult;
use n2n\core\MessageCode;
use rocket\script\entity\manage\security\MappingArrayAccess;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\persistence\orm\criteria\CriteriaConstant;

class ScriptCommandExecutionConstraint implements CommandExecutionConstraint {
	private $privilegesGrantItems = array();
	
	public function __construct(array $privilegesGrantItems) {
		$this->privilegesGrantItems = $privilegesGrantItems;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\MappingConstraint::acceptValues()
	 */
	public function acceptValues(\ArrayAccess $values) {
		foreach ($this->privilegesGrantItems as $item) {
			if ($item->acceptValues($values)) return true;
		}
		
		return false;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\MappingConstraint::acceptValue()
	 */
	public function acceptValue($id, $value) {
		foreach ($this->privilegesGrantItems as $item) {
			if ($item->acceptValue($id, $value)) return true;
		}
		
		return false;
	}

	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\MappingValidator::validate()
	 */
	public function validate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) {
		$values = new MappingArrayAccess($scriptSelectionMapping, false);
		$validationResults = array();
		foreach ($this->privilegesGrantItems as $accessGrants) {
			$validationResult = new SelectorValidationResult();
			if ($accessGrants->validateValues($values, $validationResult)) {
				return true;
			}
			$validationResults[] = $validationResult;
		}
		
		$mappingValidationResult->addError(null, new MessageCode('no_access_to_values'));
		
		return false;
	}

	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\CriteriaConstraint::applyToCriteria()
	 */
	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias) {
		$comparatorConstraints = array();
		foreach ($this->privilegesGrantItems as $privilegesGrantItem) {
			$comparatorConstraint = $privilegesGrantItem->getComparatorConstraint();
			if ($comparatorConstraint === null) return;
			$comparatorConstraints[] = $comparatorConstraint;
		}
		
		if (0 == count($comparatorConstraints)) {
			$group = $criteria->where()->andMatch(new CriteriaConstant(1), '=', new CriteriaConstant(2));
		}
		
		$group = $criteria->where()->andGroup();
		foreach ($comparatorConstraints as $comparatorConstraint) {
			$comparatorConstraint->applyToCriteriaComparator($group, $alias, false);
		}	
	}

}