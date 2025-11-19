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

namespace rocket\impl\ei\component\prop\adapter;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\util\ex\IllegalStateException;

trait PropertyNatureTrait {
	protected ?EntityProperty $entityProperty = null;
	protected ?AccessProxy $nativeAccessProxy = null;

	/**
	 * @param AccessProxy|null $nativeAccessProxy
	 */
	function __construct(?AccessProxy $nativeAccessProxy) {
		$this->nativeAccessProxy = $nativeAccessProxy;
	}

	function getNativeAccessProxy(): ?AccessProxy {
		return $this->nativeAccessProxy;
	}

	/**
	 * @return AccessProxy|null
	 */
	protected function requireNativeAccessProxy(): ?AccessProxy {
		if ($this->nativeAccessProxy === null) {
			throw new IllegalStateException('No PropertyAccessProxy assigned to ' . $this . '.');
		}

		return $this->nativeAccessProxy;
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