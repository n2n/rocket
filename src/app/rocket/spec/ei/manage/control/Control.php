<?php

namespace rocket\spec\ei\manage\control;

use n2n\web\ui\UiComponent;

interface Control {
	
	/**
	 * @param bool $reducted
	 * @return UiComponent
	 */
	public function createUiComponent(bool $reducted): UiComponent;
}

