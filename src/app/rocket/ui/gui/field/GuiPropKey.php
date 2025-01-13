<?php

namespace rocket\ui\gui\field;

use n2n\util\StringUtils;
use n2n\util\type\ArgUtils;

class GuiPropKey implements \Stringable {

	const SEPARATOR = '/';

	function __construct(private readonly string $key) {
		ArgUtils::assertTrue(self::val($key));
	}

	function __toString(): string {
		return $this->key;
	}

	static function val(string $key): bool {
		return !StringUtils::contains(self::SEPARATOR,  $key);
	}
}