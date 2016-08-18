<?php
namespace rocket\script\entity\command\impl\tree\field;

use n2n\persistence\orm\property\DefaultProperty;
use n2n\persistence\orm\property\EntityProperty;

class TreeRootIdScriptField extends TreeScriptField {
	public function getTypeName() {
		return 'Tree Root Id (Rocket)';
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
}