<?php

namespace rocket\cu\util\gui;

use rocket\cu\gui\CuMaskedEntry;
use n2n\util\HashUtils;

class CufBulkyGui {

	private CuMaskedEntry $cuMaskEntry;

	function __construct(private bool $readOnly) {
		$maskId = 'mask-' . HashUtils::base36Uniqid(false);
		$typeId = 'type-' . HashUtils::base36Uniqid(false);

		$this->cuMaskEntry = new CuMaskedEntry($maskId, $typeId, 'Unnamed Boundry');
	}

	function addField(): static {

	}




}