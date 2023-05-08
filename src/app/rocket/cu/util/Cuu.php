<?php

namespace rocket\cu\util;

use n2n\core\container\N2nContext;
use rocket\ei\util\EiuAnalyst;
use rocket\cu\util\gui\CuuGuiEntry;

class Cuu {
	private CuuAnalyst $cuuAnalyst;
	private CuuFactory $cuuFactory;

	function __construct(...$cuArgs) {
		$this->cuuAnalyst = new CuuAnalyst();
		$this->cuuAnalyst->applyEiArgs(...$cuArgs);
	}

	function getN2nContext(): N2nContext {
		return $this->cuuAnalyst->getN2nContext(true);
	}

	function getCuuAnalyst(): CuuAnalyst {
		return $this->cuuAnalyst;
	}

	/**
	 * @template T
	 * @param class-string<T> $className
	 * @return T|null
	 */
	function lookup(string $className): mixed {
		return $this->getN2nContext()->lookup($className);
	}

	function f(): CuuFactory {
		return $this->cuuFactory ?? $this->cuuFactory = new CuuFactory($this->cuuAnalyst);
	}

	function guiEntry(bool $required = true): ?CuuGuiEntry {
		return $this->cuuAnalyst->getCuuGuiEntry($required);
	}
}