<?php

namespace rocket\cu\gui\field;

use rocket\si\content\SiField;
use n2n\core\container\N2nContext;

interface CuField {

	function getSiField(): SiField;

	function getValue(): mixed;

	function validate(N2nContext $n2nContext): bool;
}