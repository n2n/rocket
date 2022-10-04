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
namespace rocket\attribute;

use rocket\spec\EiPresetMode;
use n2n\util\type\ArgUtils;

/**
 * Used together with {@link EiType} attribute to define how the EiType should be initialized.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class EiPreset {

	function __construct(public readonly ?EiPresetMode $mode = null, public readonly array $readProps = [],
			public readonly array $editProps = []) {
		ArgUtils::valArray($this->readProps, 'string');
		ArgUtils::valArray($this->editProps, 'string');
	}

	function containsReadProp(string $prop): bool {
		return in_array($prop, $this->readProps);
	}

	function containsEditProp(string $prop): bool {
		return in_array($prop, $this->readProps);
	}
}