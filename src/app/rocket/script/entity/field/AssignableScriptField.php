<?php

namespace rocket\script\entity\field;

use rocket\script\core\CompatibilityTest;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\PropertyAccessProxy;

interface AssignableScriptField extends IndependentScriptField {
	public function checkCompatibility(CompatibilityTest $compatibilityTest);
	public function setEntityProperty(EntityProperty $entiyProperty);
	public function setPropertyAccessProxy(PropertyAccessProxy $propertyAccessProxy);
}