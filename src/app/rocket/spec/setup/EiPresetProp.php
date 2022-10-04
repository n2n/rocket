<?php

namespace rocket\spec;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;

class EiPresetProp {
	function __construct(private readonly string $name, private readonly ?AccessProxy $objectPropertyAccessProxy,
			private readonly ?EntityProperty $entityProperty, private readonly bool $editable) {
	}

	/**
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * @return AccessProxy|null
	 */
	function getObjectPropertyAccessProxy() {
		return $this->objectPropertyAccessProxy;
	}

	/**
	 * @return EntityProperty|null
	 */
	function getEntityProperty() {
		return $this->entityProperty;
	}

	/**
	 * @return bool
	 */
	function isEditable() {
		return $this->editable;
	}
}