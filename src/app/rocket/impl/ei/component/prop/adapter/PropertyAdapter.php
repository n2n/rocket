<?php

namespace rocket\impl\ei\component\prop\adapter;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\util\ex\IllegalStateException;

trait PropertyAdapter {
	protected ?EntityProperty $entityProperty = null;
	protected ?AccessProxy $propertyAccessProxy = null;

	/**
	 * @param AccessProxy|null $propertyAccessProxy
	 */
	function __construct(?AccessProxy $propertyAccessProxy) {
		$this->propertyAccessProxy = $propertyAccessProxy;
	}

	/**
	 * @return AccessProxy|null
	 */
	public function getPropertyAccessProxy(): ?AccessProxy {
		return $this->propertyAccessProxy;
	}

	/**
	 * @throws IllegalStateException
	 * @return AccessProxy
	 */
	protected function requirePropertyAccessProxy(): ?AccessProxy {
		if ($this->propertyAccessProxy === null) {
			throw new IllegalStateException('No PropertyAccessProxy assigned to ' . $this . '.');
		}

		return $this->propertyAccessProxy;
	}

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
	 * @throws IllegalStateException
	 * @return EntityProperty|NULL
	 */
	protected function requireEntityProperty(): ?EntityProperty  {
		if ($this->entityProperty === null) {
			throw new IllegalStateException('No EntityProperty assigned to ' . $this);
		}

		return $this->entityProperty;
	}




}