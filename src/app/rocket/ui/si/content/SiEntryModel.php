<?php

namespace rocket\ui\si\content;

use n2n\core\container\N2nContext;

interface SiEntryModel {


	function handleInput(N2nContext $n2nContext): bool;

	/**
	 * @return string[]
	 */
	function getMessages(): array;
}