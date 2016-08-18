<?php
namespace rocket\script\entity\security;

use rocket\script\entity\manage\security\CommandExecutionConstraint;
use rocket\script\entity\manage\mapping\MappingValidationResult;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\criteria\Criteria;

class EmptyCommandExecutionConstraint implements CommandExecutionConstraint {
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\MappingConstraint::acceptValues()
	 */
	public function acceptValues(\ArrayAccess $values) {
		return true;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\MappingConstraint::acceptValue()
	 */
	public function acceptValue($id, $value) {
		return true;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\MappingValidator::validate()
	 */
	public function validate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping) {
		return true;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\CriteriaConstraint::applyToCriteria()
	 */
	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias) {	
	}

}