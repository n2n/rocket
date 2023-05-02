<?php

namespace rocket\cu\util;

use n2n\core\container\N2nContext;
use n2n\web\http\controller\impl\ControllingUtils;
use rocket\cu\gui\CuGui;
use rocket\si\content\SiGui;
use rocket\cu\util\gui\CufGui;
use rocket\si\SiPayloadFactory;

class CuuCtrl {

	private Cuu $cuf;

	function __construct(private ControllingUtils $cu) {
		$this->cuf = new Cuu($cu->getN2nContext());
	}

	function cuu(): Cuu {
		return $this->cuf;
	}

	private function forwardHtml(): bool {
		if ('text/html' == $this->cu->getRequest()->getAcceptRange()
						->bestMatch(['text/html', 'application/json'])) {
			$this->cu->forward('\rocket\core\view\anglTemplate.html');
			return true;
		}

		return false;
	}

	function forwardZone(CufGui|CuGui|SiGui $gui, string $title): void {
		if ($this->forwardHtml()) {
			return;
		}

		if ($gui instanceof CufGui) {
			$gui = $gui->getCuGui();
		}

		if ($gui instanceof CuGui) {
			$gui = $gui->toSiGui($this->cu->getRequest()->getPath()->toUrl());
		}

		$this->cu->send(SiPayloadFactory::create($gui, [], $title));
	}

	public static function from(ControllingUtils $cu): CuuCtrl {
		return new CuuCtrl($cu);
	}
}