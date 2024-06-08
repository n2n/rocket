<?php

namespace rocket\ui\si\content;

interface SiFieldModel {

	function handleInput(): bool;

	/**
	 * @return string[]
	 */
	function getMessageStrs(): array;
}