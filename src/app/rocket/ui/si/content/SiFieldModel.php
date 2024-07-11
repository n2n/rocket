<?php

namespace rocket\ui\si\content;

use n2n\core\container\N2nContext;

interface SiFieldModel {

	function handleInput(mixed $value, N2nContext $n2nContext): bool;

	function flush(N2nContext $n2nContext): void;

	/**
	 * @return string[]
	 */
	function getMessageStrs(): array;
}