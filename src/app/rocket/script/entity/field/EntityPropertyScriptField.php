<?php
namespace rocket\script\entity\field;

use n2n\persistence\orm\property\EntityProperty;

interface EntityPropertyScriptField extends ScriptField {
	/**
	 * @return EntityProperty
	 */
	public function getEntityProperty();
}