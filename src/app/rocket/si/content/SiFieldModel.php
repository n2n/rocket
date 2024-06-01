<?php

namespace rocket\si\content;

use n2n\util\magic\TaskResult;

interface SiFieldModel {

	function handleInput(): bool;

	/**
	 * @return string[]
	 */
	function getMessageStrs(): array;
}