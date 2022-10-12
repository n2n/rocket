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

use rocket\spec\setup\EiPresetMode;
use n2n\util\type\ArgUtils;
use http\Exception\InvalidArgumentException;
use n2n\util\ex\IllegalStateException;

/**
 * Used together with {@link EiType} attribute to define how the EiType should be initialized.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class EiPreset {
	public readonly	array $readProps;
	public readonly	array $editProps;

	function __construct(public readonly ?EiPresetMode $mode = null, array $readProps = [], array $editProps = []) {
		$this->readProps = $this->clean($readProps);
		$this->editProps = $this->clean($editProps);
	}

	/**
	 * @param array $props
	 * @return array
	 */
	private function clean(array $props) {
		$cleaned = [];
		foreach ($props as $key => $value) {
			if (!is_string($value)) {
				ArgUtils::valArray($value, 'string');
				throw new IllegalStateException();
			}

			if (is_string($key)) {
				$cleaned[$key] = $value;
			} else {
				$cleaned[$value] = null;
			}
		}

		return $cleaned;
	}

	function containsReadProp(string $prop): bool {
		return array_key_exists($prop, $this->readProps);
	}

	function containsEditProp(string $prop): bool {
		return array_key_exists($prop, $this->editProps);
	}

	function getPropLabel(string $prop): ?string {
		return $this->readProps[$prop] ?? $this->editProps[$prop] ?? null;
	}
}