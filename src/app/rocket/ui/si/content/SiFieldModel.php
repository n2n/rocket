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

namespace rocket\ui\si\content;

use n2n\core\container\N2nContext;
use rocket\ui\si\err\CorruptedSiDataException;

interface SiFieldModel {

	/**
	 * @param mixed $value
	 * @param N2nContext $n2nContext
	 * @return bool
	 * @throws CorruptedSiDataException if data is corrupt
	 */
	function handleInput(mixed $value, N2nContext $n2nContext): bool;

//	function save(N2nContext $n2nContext): void;

	/**
	 * @return string[]
	 */
	function getMessageStrs(): array;
}