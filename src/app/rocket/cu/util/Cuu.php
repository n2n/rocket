<?php

namespace rocket\cu\util;

use n2n\core\container\N2nContext;
use rocket\ei\util\EiuAnalyst;

class Cuu {
	private CuuFactory $cuuFactory;

	function __construct(private EiuAnalyst $eiuAnalyst) {

	}

	function getN2nContext(): N2nContext {
		return $this->eiuAnalyst->getN2nContext(true);
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
		return $this->cuuFactory ?? $this->cuuFactory = new CuuFactory($this->eiuAnalyst);
	}
}