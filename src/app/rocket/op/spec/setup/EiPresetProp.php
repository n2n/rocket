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
namespace rocket\op\spec\setup;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\op\ei\EiPropPath;

class EiPresetProp {
	function __construct(private readonly EiPropPath $eiPropPath, private readonly PropertyAccessProxy $propertyAccessProxy,
			private readonly ?EntityProperty $entityProperty, private readonly bool $editable,
			private readonly string $label, private readonly bool $autoDetected) {
	}

	function getEiPropPath(): EiPropPath {
		return $this->eiPropPath;
	}

	/**
	 * @return PropertyAccessProxy
	 */
	function getPropertyAccessProxy() {
		return $this->propertyAccessProxy;
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

	function getLabel() {
		return $this->label;
	}

	function isAutoDetected(): bool {
		return $this->autoDetected;
	}
}