<?php

namespace rocket\ei\manage\control;

use n2n\web\ui\UiComponent;

interface Control {
	
	public function isStatic(): bool;
	
	/**
	 * @param bool $reducted
	 * @return UiComponent
	 */
	public function createUiComponent(array $attrs = array()): UiComponent;
}

