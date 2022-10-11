<?php

namespace rocket\impl\ei\component\prop\adapter;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;

trait PropertyAdapter {
	protected ?EntityProperty $entityProperty;
	protected ?AccessProxy $objectPropertyAccessProxy;

	/**
	 * @param EntityProperty|null $entityProperty
	 */
	public function setEntityProperty(?EntityProperty $entityProperty): void {
		$this->entityProperty = $entityProperty;
	}

	/**
	 * @return EntityProperty|null
	 */
	public function getEntityProperty(): ?EntityProperty {
		return $this->entityProperty;
	}

	/**
	 * @return AccessProxy|null
	 */
	public function getObjectPropertyAccessProxy(): ?AccessProxy {
		return $this->objectPropertyAccessProxy;
	}

	/**
	 * @param AccessProxy|null $objectPropertyAccessProxy
	 */
	public function setObjectPropertyAccessProxy(?AccessProxy $objectPropertyAccessProxy): void {
		$this->objectPropertyAccessProxy = $objectPropertyAccessProxy;
	}
}