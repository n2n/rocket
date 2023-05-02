<?php

namespace rocket\cu\util;

use n2n\core\container\N2nContext;

class Cuu {
	private CuuFactory $cuuFactory;

	function __construct(private N2nContext $n2NContext) {

	}

	function f(): CuuFactory {
		return $this->cuuFactory ?? $this->cuuFactory = new CuuFactory();
	}
}