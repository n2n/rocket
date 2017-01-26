<?php

namespace rocket\spec\ei\component\field\impl\string\cke\model;

use n2n\l10n\N2nLocale;
use n2n\web\ui\view\View;

abstract class CkeLinkProviderAdapter implements CkeLinkProvider {
	
	public function buildUrl(string $key, View $view, N2nLocale $n2nLocale) {
		return null;
	}
	
	/**
	 * @return bool
	 */
	public function isOpenInNewWindow(): bool {
		return false;
	}
}