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
namespace rocket\ui\si\content\impl;

use rocket\ui\si\err\CorruptedSiDataException;
use n2n\core\container\N2nContext;

abstract class InSiFieldAdapter extends SiFieldAdapter {

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::isReadOnly()
	 */
	function isReadOnly(): bool {
		return false;
	}

	abstract function getValue(): mixed;

	final function handleInput(array $data, N2nContext $n2nContext): bool {
		$valueValid = $this->handleInputValue($data, $n2nContext);
		$valid = $this->getModel()?->handleInput($this->getValue(), $n2nContext);

		return $valueValid && $valid;
	}

	final function flush(N2nContext $n2nContext): void {
		$this->getModel()?->save($n2nContext);
	}

	/**
	 * @param array $data
	 * @param N2nContext $n2nContext
	 * @return bool
	 * @throws CorruptedSiDataException
	 */
	protected abstract function handleInputValue(array $data, N2nContext $n2nContext): bool;
}

