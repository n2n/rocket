<?php

namespace rocket\cu\util;

use n2n\core\container\N2nContext;
use rocket\ei\util\EiuAnalyst;

class Cuu {
	private CuuFactory $cuuFactory;

	function __construct(private EiuAnalyst $eiuAnalyst) {

	}

	function f(): CuuFactory {
		return $this->cuuFactory ?? $this->cuuFactory = new CuuFactory($this->eiuAnalyst);
	}
}