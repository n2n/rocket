<?php
namespace rocket\script\entity\field;

use rocket\script\entity\field\impl\IndependentScriptFieldAdapter;

class NotManagedPropertyScriptField extends IndependentScriptFieldAdapter  {
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\ScriptField::getTypeName()
	 */
	public function getTypeName() {
		return 'Not Managed';
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\EntityPropertyScriptField::isCompatibleWith()
	 */
	public function isCompatibleWith(\n2n\persistence\orm\property\EntityProperty $entityProperty) {
		return true;
	}
}