<?php

namespace rocket\cu\util;

use n2n\core\container\N2nContext;
use n2n\web\http\controller\impl\ControllingUtils;

class CuuCtrl {

	private Cuu $cuf;

	function __construct(private ControllingUtils $cu) {
		$this->cuf = new Cuu($cu->getN2nContext());
	}

	function cuf(): Cuu {
		return $this->cuf;
	}

	function forwardZone(CuGui $cuGui) {

	}

	public static function from(ControllingUtils $cu): CuuCtrl {
		return new CuuCtrl($cu);
	}
}