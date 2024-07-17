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

namespace rocket\op\cu\gui;

use rocket\op\cu\gui\field\CuField;
use rocket\ui\si\content\SiEntry;
use phpbob\representation\ex\UnknownElementException;
use rocket\ui\si\api\request\SiEntryInput;
use n2n\core\container\N2nContext;
use rocket\si\input\SiInputError;
use rocket\ui\si\err\CorruptedSiDataException;

class CuGuiEntry {
	/**
	 * @var array
	 */
	private array $cuFields = [];
	private SiEntry $siEntry;

	function __construct() {
		$this->siEntry = new SiEntry(null, null);
	}

	function containsCuField(string $id): bool {
		return isset($this->cuFields[$id]);
	}

	function putCuField(string $id, CuField $cuField): static {
		$this->siEntry->putField($id, $cuField->getSiField());
		$this->cuFields[$id] = $cuField;
		return $this;
	}

	function getCuField(string $id): CuField {
		if (isset($this->cuFields[$id])) {
			return $this->cuFields[$id];
		}

		throw new UnknownElementException('Unknown CuField id: ' . $id);
	}

	function getSiEntry(): SiEntry {
		return $this->siEntry;
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput, N2nContext $n2nContext): bool {
		$this->siEntry->handleEntryInput($siEntryInput, $n2nContext);

		$failed = false;

		foreach ($this->cuFields as $cuField) {
			if (!$cuField->validate($n2nContext)) {
				$failed = true;
			}
		}

		return !$failed;
	}
}